<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\RcoCallback;

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
use Resursbank\Ecom\Lib\Model\Network\Auth\Basic;
use Resursbank\Ecom\Module\RcoCallback\Models\RegisterCallback\DigestConfiguration;
use Resursbank\Ecom\Module\RcoCallback\Models\RegisterCallback\Request;
use Resursbank\Ecom\Module\RcoCallback\Repository;

/**
 * Tests for RCO callback module Repository class.
 */
class RepositoryTest extends TestCase
{
    /**
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
    protected function setUp(): void
    {
        // Set up Config object
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: new Basic(
                username: $_ENV['BASIC_AUTH_USERNAME'],
                password: $_ENV['BASIC_AUTH_PASSWORD']
            )
        );

        // Clear existing callbacks
        $eventNames = ['TEST', 'UNFREEZE', 'BOOKED', 'UPDATE'];

        foreach ($eventNames as $eventName) {
            Repository::deleteCallback(eventName: $eventName);
        }

        parent::setUp();
    }

    /**
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
    protected function tearDown(): void
    {
        // Clear existing callbacks
        $eventNames = ['TEST', 'UNFREEZE', 'BOOKED', 'UPDATE'];

        foreach ($eventNames as $eventName) {
            Repository::deleteCallback(eventName: $eventName);
        }

        parent::tearDown();
    }

    /**
     * Verify that we can register, fetch and delete callbacks
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
    public function testRegisterGetAndDeleteCallback(): void
    {
        $auth = Config::getBasicAuth();

        if ($auth === null) {
            $this->fail(message: 'Basic auth is not configured.');
        }

        $eventName = 'BOOKED';
        $request = new Request(
            uriTemplate: 'https://example.com/dummy?id={paymentId}&amp;hash={digest}',
            basicAuthUserName: $auth->username,
            basicAuthPassword: $auth->password,
            digestConfiguration: new DigestConfiguration(
                digestAlgorithm: 'SHA1',
                digestSalt: 'FOO',
                digestParameters: [
                    'paymentId',
                ]
            )
        );

        Repository::registerCallback(eventName: $eventName, request: $request);

        $registeredCallback = Repository::getCallback(eventName: $eventName);

        $deleteResponse = Repository::deleteCallback(eventName: $eventName);

        $this->assertSame(
            expected: $eventName,
            actual: $registeredCallback->eventType
        );
        $this->assertNotEmpty(actual: $registeredCallback->uriTemplate);
        $this->assertSame(expected: 200, actual: $deleteResponse);
    }

    /**
     * Verify that fetching all registered callbacks works
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
    public function testGetCallbacks(): void
    {
        $auth = Config::getBasicAuth();

        if ($auth === null) {
            $this->fail(message: 'Basic auth is not configured.');
        }

        $eventNames = ['BOOKED', 'UPDATE'];
        $request = new Request(
            uriTemplate: 'https://example.com/dummy?id={paymentId}&amp;hash={digest}',
            basicAuthUserName: $auth->username,
            basicAuthPassword: $auth->password,
            digestConfiguration: new DigestConfiguration(
                digestAlgorithm: 'SHA1',
                digestSalt: 'FOO',
                digestParameters: [
                    'paymentId',
                ]
            )
        );

        foreach ($eventNames as $eventName) {
            Repository::registerCallback(
                eventName: $eventName,
                request: $request
            );
        }

        $response = Repository::getCallbacks();

        $this->assertCount(
            expectedCount: 2,
            haystack: $response->toArray()
        );
    }

    /**
     * Verify that attempting to get an unregistered callback throws an EmptyValueException
     *
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @throws IllegalValueException
     */
    public function testGetCallbackFailure(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        Repository::getCallback(eventName: 'UPDATE');
    }
}
