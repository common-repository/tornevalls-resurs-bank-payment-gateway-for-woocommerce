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
use Resursbank\Ecom\Module\Payment\Enum\ActionType;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\EcomTest\Utilities\MockSigner;

/**
 * Tests for MAPI Payment Cancel class.
 */
class CancelTest extends TestCase
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
     * Verify that canceling an entire payment works as intended
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
    public function testCancelEntirePayment(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Cancel payment
        $response = Repository::cancel(paymentId: $payment->id);

        // Assert that cancel went through
        $this->assertEquals(expected: $payment->id, actual: $response->id);
        $this->assertNotNull(actual: $response->order);
        $this->assertNotNull(actual: $payment->order);
        $this->assertTrue(condition: isset($response->order->actionLog[1]));

        $actionLog = $response->order->actionLog[1];

        $this->assertInstanceOf(expected: ActionLog::class, actual: $actionLog);

        $this->assertEquals(
            expected: ActionType::CANCEL,
            actual: $actionLog->type
        );
        $this->assertEquals(
            expected: $payment->order->totalOrderAmount,
            actual: $response->order->totalOrderAmount
        );
        $this->assertEquals(
            expected: $response->order->totalOrderAmount,
            actual: $response->order->canceledAmount
        );
    }

    /**
     * Verify that cancelling a single order line works as intended
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
    public function testCancelWithOrderLines(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Cancel one order line
        $orderLine = new OrderLine(
            description: 'Android',
            reference: 'T-800',
            quantityUnit: 'st',
            quantity: 2.00,
            vatRate: 25.00,
            unitAmountIncludingVat: 150.75,
            totalAmountIncludingVat: 301.5,
            totalVatAmount: 60.3,
            type: OrderLineType::PHYSICAL_GOODS
        );
        $response = Repository::cancel(
            paymentId: $payment->id,
            orderLines: new OrderLineCollection(data: [$orderLine])
        );

        // Assert that cancel went through
        $this->assertEquals(expected: $payment->id, actual: $response->id);
        $this->assertNotNull(actual: $response->order);
        $this->assertNotNull(actual: $payment->order);
        $this->assertTrue(condition: isset($payment->order->actionLog[0]));
        $this->assertTrue(condition: isset($response->order->actionLog[1]));

        $actionLog1 = $payment->order->actionLog[0];
        $actionLog2 = $response->order->actionLog[1];

        $this->assertInstanceOf(
            expected: ActionLog::class,
            actual: $actionLog1
        );
        $this->assertInstanceOf(
            expected: ActionLog::class,
            actual: $actionLog2
        );
        $this->assertTrue(condition: isset($actionLog1->orderLines[0]));
        $this->assertTrue(condition: isset($actionLog2->orderLines[0]));

        $orderLine1 = $actionLog1->orderLines[0];
        $orderLine2 = $actionLog2->orderLines[0];

        $this->assertInstanceOf(
            expected: OrderLine::class,
            actual: $orderLine1
        );
        $this->assertInstanceOf(
            expected: OrderLine::class,
            actual: $orderLine2
        );
        $this->assertEquals(expected: $orderLine1, actual: $orderLine2);
        $this->assertEquals(
            expected: $orderLine1->totalAmountIncludingVat,
            actual: $response->order->canceledAmount
        );
    }

    /**
     * Verify that canceling with creator argument results in specified creator value being present in action log
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
    public function testCancelWithCreator(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Cancel order
        $creator = 'Foobar';
        $response = Repository::cancel(
            paymentId: $payment->id,
            creator: $creator
        );

        $this->assertTrue(condition: isset($response->order->actionLog[1]));

        $actionLog = $response->order->actionLog[1];

        $this->assertInstanceOf(expected: ActionLog::class, actual: $actionLog);

        // Assert that creator argument is present in action log
        $this->assertEquals(expected: $payment->id, actual: $response->id);
        $this->assertNotNull(actual: $response->order);
        $this->assertEquals(expected: $creator, actual: $actionLog->creator);
    }
}
