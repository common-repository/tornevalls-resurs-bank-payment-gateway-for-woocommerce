<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment;

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
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Model\Payment\Metadata;
use Resursbank\Ecom\Lib\Model\Payment\Order;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLogCollection;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\EcomTest\Utilities\MockSigner;

/**
 * Integration tests for CreatePayment repository.
 */
class RepositoryTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );

        parent::setUp();
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
    public function testCreatePayment(): void
    {
        $orderLines = new OrderLineCollection(
            data: [
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 301.5,
                    description: 'asdasdasd',
                    reference: 'T-800',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 150.75,
                    totalVatAmount: 60.3
                ),
            ]
        );

        $createdPayment = Repository::create(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
            orderLines: $orderLines
        );

        /** @var Order $order */
        $order = $createdPayment->order;

        /** @var ActionLogCollection $actionLog */
        $actionLog = $order->actionLog;

        if (empty($actionLog->toArray())) {
            throw new MissingKeyException(
                message: 'actionLog contains no entries'
            );
        }

        /** @var Order\ActionLog $actionLogEntry */
        $actionLogEntry = $actionLog[0];

        /** @var OrderlineCollection $orderLines */
        $orderLines = $actionLogEntry->orderLines;

        if (!isset($orderLines[0])) {
            throw new MissingKeyException(
                message: 'orderLines contains no entries'
            );
        }

        /** @var OrderLine $orderLine */
        $orderLine = $orderLines[0];

        /** @var OrderLine $createdOrderLine */
        $createdOrderLine = $orderLines[0];

        $this->assertEquals(
            expected: $orderLine->description,
            actual: $createdOrderLine->description
        );
        $this->assertEquals(
            expected: $orderLine->vatRate,
            actual: $createdOrderLine->vatRate
        );
        $this->assertEquals(
            expected: $orderLine->reference,
            actual: $createdOrderLine->reference
        );
    }

    /**
     * Assert that it's possible to create a new payment with metadata on it.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws MissingKeyException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCreatePaymentWithMetadata(): void
    {
        $orderLines = new OrderLineCollection(
            data: [
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 301.5,
                    description: 'asdasdasd',
                    reference: 'T-800',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 150.75,
                    totalVatAmount: 60.3
                ),
            ]
        );
        $metadata = new Metadata(
            custom: new Metadata\EntryCollection(
                data: [
                    new Metadata\Entry(
                        key: 'foo',
                        value: 'bar'
                    ),
                    new Metadata\Entry(
                        key: 'fnord',
                        value: 'baz'
                    ),
                ]
            )
        );

        $createdOrder = Repository::create(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
            orderLines: $orderLines,
            metadata: $metadata
        );

        if (!isset($createdOrder->order->actionLog[0])) {
            throw new MissingKeyException(
                message: 'actionLog contains no entries'
            );
        }

        /** @var Metadata $createdMetadata */
        $createdMetadata = $createdOrder->metadata;

        if ($metadata->custom === null) {
            throw new MissingKeyException(
                message: '$metadata contains no custom property'
            );
        }

        if ($createdMetadata->custom === null) {
            throw new MissingKeyException(
                message: '$createdMetadata contains no custom property'
            );
        }

        $this->assertEqualsCanonicalizing(
            expected: $metadata->custom->toArray(),
            actual: $createdMetadata->custom->toArray()
        );
    }

    /**
     * Verify that updateOrderLines actually replaces order lines.
     *
     * @throws Exception
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testReplaceOrderLines(): void
    {
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);
        MockSigner::approve(payment: $payment);

        // Fetch order
        $payment = Repository::get(paymentId: $payment->id);

        $orderLines = new OrderLineCollection(data: [
            new OrderLine(
                quantity: 1,
                quantityUnit: 'st',
                vatRate: 25,
                totalAmountIncludingVat: 100,
                description: 'One hundred',
                type: OrderLineType::PHYSICAL_GOODS
            ),
        ]);
        $updatedPayment = Repository::updateOrderLines(
            paymentId: $payment->id,
            orderLines: $orderLines
        );

        if ($updatedPayment->order === null) {
            throw new Exception(message: 'updatedPayment order object is null');
        }

        /** @var Order\ActionLog $updatedActionLog */
        $updatedActionLog = $updatedPayment->order->actionLog[array_key_last(
            array: $updatedPayment->order->actionLog->toArray()
        )];
        $updatedOrderLines = $updatedActionLog->orderLines;

        $orderLineSum = 0.0;

        /** @var OrderLine $orderLine */
        foreach ($orderLines as $orderLine) {
            $orderLineSum += $orderLine->totalAmountIncludingVat;
        }

        $updatedOrderLineSum = 0.0;

        /** @var OrderLine $orderLine */
        foreach ($updatedOrderLines as $orderLine) {
            $updatedOrderLineSum += $orderLine->totalAmountIncludingVat;
        }

        $this->assertEquals(
            expected: $payment->id,
            actual: $updatedPayment->id
        );
        $this->assertCount(
            expectedCount: count($orderLines),
            haystack: $updatedOrderLines
        );
        $this->assertEquals(
            expected: $orderLineSum,
            actual: $updatedOrderLineSum
        );
    }

    /**
     * Assert TaskStatusDetails->completed for an associated Payment remains
     * "false" until the payment is actually completed, at which point it should
     * change to "true".
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Exception
     */
    public function testGetTaskStatusDetails(): void
    {
        $payment = $this->createPayment(
            orderReference: $this->generateOrderReference()
        );

        $task = Repository::getTaskStatusDetails(paymentId: $payment->id);
        $this->assertFalse(condition: $task->completed);
    }
}
