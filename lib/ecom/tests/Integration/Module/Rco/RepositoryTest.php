<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rco;

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
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LogLevel;
use Resursbank\Ecom\Lib\Model\Network\Auth\Basic;
use Resursbank\Ecom\Module\Rco\Models\Address;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Customer;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Request;
use Resursbank\Ecom\Module\Rco\Models\OrderLine;
use Resursbank\Ecom\Module\Rco\Models\OrderLineCollection;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Request as UpdateRequest;
use Resursbank\Ecom\Module\Rco\Models\UpdatePaymentReference\Request as UpdatePaymentReferenceRequest;
use Resursbank\Ecom\Module\Rco\Repository;

/**
 * Tests for RCO module Repository class.
 */
final class RepositoryTest extends TestCase
{
    private string $orderReference;
    private Request $request;

    /**
     * Set up prerequisites for testing
     *
     * @throws IllegalTypeException
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->orderReference = bin2hex(string: random_bytes(length: 8));
        $this->request = new Request(
            orderLines: new OrderLineCollection(data: [
                new OrderLine(
                    artNo: 'sku123',
                    description: 'My product',
                    quantity: 1,
                    unitMeasure: 'pc',
                    unitAmountWithoutVat: 20,
                    vatPct: 25
                ),
            ]),
            customer: new Customer(
                governmentId: '198305147715',
                mobile: '46701234567',
                email: 'test@hosted.resurs',
                deliveryAddress: new Address(
                    firstName: 'Vincent',
                    lastName: 'Williamsson Alexandersson',
                    addressRow1: 'Glassgatan 15',
                    postalArea: 'Göteborg',
                    postalCode: '41655',
                    countryCode: 'SE'
                )
            ),
            successUrl: 'https://example.com/success',
            backUrl: 'https://example.com/checkout',
            shopUrl: 'https://example.com'
        );

        $basicAuth = new Basic(
            username: $_ENV['BASIC_AUTH_USERNAME'],
            password: $_ENV['BASIC_AUTH_PASSWORD']
        );

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: $basicAuth,
            logLevel: LogLevel::DEBUG,
            isProduction: false
        );

        parent::setUp();
    }

    /**
     * Verify that InitPayment works
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
     */
    public function testInitPayment(): void
    {
        $response = Repository::initPayment(
            request: $this->request,
            orderReference: $this->orderReference
        );

        $this::assertSame(
            expected: $this->request->customer->governmentId,
            actual: $response->customer?->governmentId ?? ''
        );

        if ($response->iframe === null) {
            $this->fail(message: 'No iframe found in response.');
        }

        $this::assertSame(
            expected: '<iframe',
            actual: substr(string: $response->iframe, offset: 0, length: 7)
        );
    }

    /**
     * Verify that a valid UpdatePayment request returns http 200 and the payment session id
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @throws ConfigException
     * @throws IllegalValueException
     */
    public function testUpdatePayment(): void
    {
        $session = Repository::initPayment(
            request: $this->request,
            orderReference: $this->orderReference
        );

        $request = new UpdateRequest(
            orderLines: new OrderLineCollection(
                data: [
                    new OrderLine(
                        artNo: 'Updated-1234',
                        description: 'Updated product',
                        quantity: 2,
                        unitMeasure: 'pc',
                        unitAmountWithoutVat: 20,
                        vatPct: 25
                    ),
                ]
            )
        );

        $response = Repository::updatePayment(
            request: $request,
            orderReference: $this->orderReference
        );

        $this::assertSame(expected: 200, actual: $response->code);
        $this::assertSame(
            expected: $session->paymentSessionId,
            actual: $response->message
        );
    }

    /**
     * Verify that a 404 response is given when attempting to update a nonexistent order.
     *
     * @throws ReflectionException
     * @throws IllegalTypeException
     * @throws Exception
     */
    public function testUpdatePaymentWrongOrderReference(): void
    {
        Repository::initPayment(
            request: $this->request,
            orderReference: $this->orderReference
        );

        $request = new UpdateRequest(
            orderLines: new OrderLineCollection(
                data: [
                    new OrderLine(
                        artNo: 'Updated-1234',
                        description: 'Updated product',
                        quantity: 2,
                        unitMeasure: 'pc',
                        unitAmountWithoutVat: 20,
                        vatPct: 25
                    ),
                ]
            )
        );

        $this->expectException(exception: CurlException::class);

        try {
            Repository::updatePayment(
                request: $request,
                orderReference: $this->orderReference . bin2hex(
                    string: random_bytes(length: 8)
                )
            );
        } catch (CurlException $e) {
            $this::assertSame(expected: 404, actual: $e->httpCode);
            throw $e;
        }
    }

    /**
     * Verify that a valid UpdatePaymentReference request returns HTTP 200 and the order reference
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testUpdatePaymentReference(): void
    {
        Repository::initPayment(
            request: $this->request,
            orderReference: $this->orderReference
        );

        $newPaymentReference = bin2hex(string: random_bytes(length: 8));
        $request = new UpdatePaymentReferenceRequest(
            paymentReference: $newPaymentReference
        );

        $response = Repository::updatePaymentReference(
            request: $request,
            orderReference: $this->orderReference
        );
        $this::assertSame(expected: 200, actual: $response->code);
    }
}
