<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection EfferentObjectCouplingInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Api;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\EcomTest\Utilities\MockSigner;

/**
 * Tests for MAPI Payment Capture class.
 */
class CaptureTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );
    }

    /**
     * Generate a dummy order reference
     *
     * @throws Exception
     */
    private function generateOrderReference(): string
    {
        return bin2hex(string: random_bytes(length: 12));
    }

    /**
     * Make API call to create payment
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    private function createPayment(string $orderReference): Payment
    {
        /** @noinspection DuplicatedCode */
        return Repository::create(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
            orderLines: new OrderLineCollection(data: [
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 301.5,
                    description: 'Android',
                    reference: 'T-800',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 150.75,
                    totalVatAmount: 60.3
                ),
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 301.5,
                    description: 'Robot',
                    reference: 'T-1000',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 150.75,
                    totalVatAmount: 60.3
                ),
            ]),
            orderReference: $orderReference,
            customer: new Customer(
                deliveryAddress: new Address(
                    addressRow1: 'Glassgatan 15',
                    postalArea: 'Göteborg',
                    postalCode: '41655',
                    countryCode: CountryCode::SE
                ),
                customerType: CustomerType::NATURAL,
                contactPerson: 'Vincent',
                email: 'test@hosted.resurs',
                governmentId: '198305147715',
                mobilePhone: '46701234567',
                deviceInfo: new DeviceInfo()
            )
        );
    }

    /**
     * Verify that capturing an entire order works
     *
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws IllegalTypeException
     * @throws Exception
     */
    public function testCaptureEntirePayment(): void
    {
        $orderReference = $this->generateOrderReference();
        // Create payment
        $payment = $this->createPayment(orderReference: $orderReference);
        $originalId = $payment->id;

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture payment
        $response = Repository::capture(paymentId: $originalId);

        // Assert that payment has been captured in full
        $this->assertNotNull(actual: $response->order);
        $this->assertEquals(expected: $originalId, actual: $response->id);
        $this->assertEquals(
            expected: $response->order->totalOrderAmount,
            actual: $response->order->capturedAmount
        );
    }

    /**
     * Verify that capturing a single specified order line works
     *
     * @throws Exception
     */
    public function testCaptureSingleOrderLine(): void
    {
        $orderReference = $this->generateOrderReference();
        // Create payment with multiple order lines
        $payment = $this->createPayment(orderReference: $orderReference);

        MockSigner::approve(payment: $payment);

        $orderLines = new OrderLineCollection(data: [
            new OrderLine(
                description: 'Android',
                reference: 'T-800',
                quantityUnit: 'st',
                quantity: 2.00,
                vatRate: 25.00,
                unitAmountIncludingVat: 150.75,
                totalAmountIncludingVat: 301.5,
                totalVatAmount: 60.3,
                type: OrderLineType::PHYSICAL_GOODS
            ),
        ]);

        // Capture single order line
        $response = Repository::capture(
            paymentId: $payment->id,
            orderLines: $orderLines
        );

        // Assert that only this order line has been captured
        $this->assertEquals(expected: $payment->id, actual: $response->id);
        $this->assertNotNull(actual: $response->order);
        $this->assertCount(
            expectedCount: 2,
            haystack: $response->order->actionLog
        );
    }

    /**
     * Verify that capturing with a transaction ID works
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testCaptureWithTransactionId(): void
    {
        $orderReference = $this->generateOrderReference();
        // Create payment
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture and specify transaction id
        $transactionId = $this->generateOrderReference();
        $response = Repository::capture(
            paymentId: $payment->id,
            transactionId: $transactionId
        );

        // Verify that capture worked as intended
        $this->assertNotNull(actual: $response->order);
        $this->assertTrue(condition: isset($response->order->actionLog[1]));

        $actionLog = $response->order->actionLog[1];

        $this->assertInstanceOf(expected: ActionLog::class, actual:$actionLog);
        $this->assertEquals(
            expected: $transactionId,
            actual: $actionLog->transactionId
        );
    }

    /**
     * Verify that capturing with an invoice ID works
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testCaptureWithInvoiceId(): void
    {
        $orderReference = $this->generateOrderReference();
        // Create payment
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture and specify transaction id
        $invoiceId = $this->generateOrderReference();
        $orderLines = new OrderLineCollection(data: [
            new OrderLine(
                quantity: 2.00,
                quantityUnit: 'st',
                vatRate: 25.00,
                totalAmountIncludingVat: 301.5,
                description: 'Android',
                reference: 'T-800',
                type: OrderLineType::PHYSICAL_GOODS,
                unitAmountIncludingVat: 150.75,
                totalVatAmount: 60.3
            ),
        ]);
        $response = Repository::capture(
            paymentId: $payment->id,
            orderLines: $orderLines,
            invoiceId: $invoiceId
        );

        // Verify that capture worked as intended
        $this->assertNotNull(actual: $response->order);
        $this->assertEquals(expected: $payment->id, actual: $response->id);
        $this->assertCount(
            expectedCount: 2,
            haystack: $response->order->actionLog
        );
    }
}
