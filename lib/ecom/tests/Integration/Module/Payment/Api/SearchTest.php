<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

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
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\EcomTest\Utilities\MockSigner;

/**
 * Test that searchPayment works.
 */
class SearchTest extends TestCase
{
    private const GOVERNMENT_ID = '198305147715';

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
                governmentId: self::GOVERNMENT_ID,
                mobilePhone: '46701234567',
                deviceInfo: new DeviceInfo()
            )
        );
    }

    /**
     * Reference is currently required to have if we want to run live tests.
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Exception
     */
    public function testSearchOrderReference(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        $paymentCollection = Repository::search(
            storeId: $_ENV['STORE_ID'],
            orderReference: $orderReference
        )->toArray();

        /** @var Payment|null $fetched */
        $fetched = $paymentCollection[0] ?? null;

        $this->assertSame(expected: $payment->id, actual: $fetched?->id ?? '');
    }

    /**
     * @throws ValidationException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testSearchWithGovernmentId(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        $paymentCollection = Repository::search(
            storeId: $_ENV['STORE_ID'],
            orderReference: $orderReference,
            governmentId: self::GOVERNMENT_ID
        )->toArray();

        self::assertArrayHasKey(key: 0, array: $paymentCollection);

        /** @var Payment|null $fetched */
        $fetched = $paymentCollection[0] ?? null;

        $this->assertSame(
            expected: $payment->id,
            actual: $fetched instanceof Payment ? $fetched->id : ''
        );
    }
}
