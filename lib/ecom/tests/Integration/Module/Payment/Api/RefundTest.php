<?php

/** @noinspection EfferentObjectCouplingInspection */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

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
 * Tests for MAPI Payment Refund class.
 */
class RefundTest extends TestCase
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
     * Verify that refunding an entire order works as intended
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ValidationException
     * @throws ReflectionException
     * @throws Exception
     * @todo This test will sometimes fail, stating received timestamp is not valid. On separate re-run worked fine.
     */
    public function testRefundEntirePayment(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture payment
        Repository::capture(paymentId: $payment->id);

        // Refund entire payment
        $refundResponse = Repository::refund(paymentId: $payment->id);

        // Assert that entire payment has been refunded
        $this->assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        $this->assertNotNull(actual: $refundResponse->order);
        $this->assertNotNull(actual: $payment->order);
        $this->assertEquals(
            expected: $payment->order->totalOrderAmount,
            actual: $refundResponse->order->refundedAmount
        );
    }

    /**
     * Verify that refunding a single captured order line works
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
     * @throws Exception
     */
    public function testRefundSingleOrderLine(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture
        Repository::capture(paymentId: $payment->id);

        // Refund single order line
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
        $refundResponse = Repository::refund(
            paymentId: $payment->id,
            orderLines: $orderLines
        );

        // Assert that only specified order line has been refunded
        $this->assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        $this->assertNotNull(actual: $refundResponse->order);

        $orderLine = $orderLines[0];

        $this->assertInstanceOf(expected: OrderLine::class, actual: $orderLine);

        $this->assertEquals(
            expected: $orderLine->totalAmountIncludingVat,
            actual: $refundResponse->order->refundedAmount
        );
    }

    /**
     * Verify that refunding with a transaction id works
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
     * @throws Exception
     */
    public function testRefundWithTransactionId(): void
    {
        // Create payment
        /** @noinspection DuplicatedCode */
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture
        Repository::capture(paymentId: $payment->id);

        // Refund
        $transactionId = $this->generateOrderReference();
        $refundResponse = Repository::refund(
            paymentId: $payment->id,
            transactionId: $transactionId
        );

        // Assert that transaction id is present in action log
        $this->assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        $this->assertNotNull(actual: $refundResponse->order);
        $this->assertTrue(
            condition: isset($refundResponse->order->actionLog[2])
        );

        $actionLog = $refundResponse->order->actionLog[2];

        $this->assertInstanceOf(expected: ActionLog::class, actual: $actionLog);

        $this->assertEquals(
            expected: $transactionId,
            actual: $actionLog->transactionId
        );
    }

    /**
     * Verify that refunding with creator specified works
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
     * @throws Exception
     */
    public function testRefundWithCreator(): void
    {
        // Create payment
        /** @noinspection DuplicatedCode */
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture
        Repository::capture(paymentId: $payment->id);

        // Refund
        $creator = $this->generateOrderReference();
        $refundResponse = Repository::refund(
            paymentId: $payment->id,
            creator: $creator
        );

        // Assert that transaction id is present in action log
        $this->assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        $this->assertNotNull(actual: $refundResponse->order);
        $this->assertTrue(
            condition: isset($refundResponse->order->actionLog[2])
        );

        $actionLog = $refundResponse->order->actionLog[2];

        $this->assertInstanceOf(expected: ActionLog::class, actual: $actionLog);

        $this->assertEquals(expected: $creator, actual: $actionLog->creator);
    }
}
