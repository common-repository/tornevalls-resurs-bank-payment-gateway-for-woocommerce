<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Action;

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
use Resursbank\Ecom\Module\Action\Repository as ActionRepository;
use Resursbank\Ecom\Module\Payment\Enum\ActionType;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\EcomTest\Utilities\MockSigner;

/**
 * Integration tests for Action repository.
 *
 * @noinspection EfferentObjectCouplingInspection
 */
class RepositoryTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        Config::setup(
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
     * Create new payment to utilise in testing.
     *
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws ValidationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function createPayment(string $orderReference): Payment
    {
        $orderLines = new OrderLineCollection(
            data: [
                new OrderLine(
                    quantity: 3.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 155.58,
                    description: 'Big t-shirt',
                    reference: 'T-9',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 51.86,
                    totalVatAmount: 31.12
                ),
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 750.50,
                    description: 'Teapot',
                    reference: 'teatime',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 375.25,
                    totalVatAmount: 150.1
                ),
                new OrderLine(
                    quantity: 10.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 5200,
                    description: 'Vampire doll',
                    reference: 'vamp',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 520,
                    totalVatAmount: 1040
                ),
            ]
        );

        return Repository::create(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
            orderLines: $orderLines,
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
     * Create a new payment, sign it, capture it. Extract the ActionLog entries
     * from the capture response. Find the entry which matches the CAPTURE
     * action. Execute an API request to fetch the ActionLog object matching
     * the id extracted from the capture response, to make sure we can fetch
     * a separate ActionLog entry, and that it matches the entry from our
     * capture response (e.g. they are the same entry).
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
    public function testActionLog(): void
    {
        // Create a new payment.
        $payment = $this->createPayment(
            orderReference: $this->generateOrderReference()
        );

        // Confirm payment was created.
        $this->assertNotNull(actual: $payment->order);
        $this->assertNotEmpty(actual: $payment->order->actionLog);

        // Sign payment.
        MockSigner::approve(payment: $payment);

        // Capture payment.
        $capture = Repository::capture(paymentId: $payment->id);

        // Assert captured worked.
        $this->assertNotNull(actual: $capture->order);
        $this->assertNotEmpty(actual: $capture->order->actionLog);

        // Get ActionLog object matching CAPTURE action from capture response.
        $action = null;

        /** @var ActionLog $entry */
        foreach ($capture->order->actionLog as $entry) {
            if ($entry->type === ActionType::CAPTURE) {
                $action = $entry;
                break;
            }
        }

        $this->assertNotNull(
            actual: $action,
            message: 'Failed to find CAPTURE action.'
        );

        $action2 = ActionRepository::getAction(
            paymentId: $payment->id,
            actionId: $action->actionId
        );

        $this->assertEquals(expected: $action, actual: $action2);
    }
}
