<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network\Curl;

use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl\ErrorHandler;

/**
 * This class will test the curl error handler.
 */
class ErrorHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::setup();
    }

    /**
     * Assert validate() throws IllegalTypeException when body isn't string.
     *
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws AuthException
     */
    public function testValidateThrowsWithoutJsonContent(): void
    {
        $this->expectException(exception: IllegalTypeException::class);

        $handler = new ErrorHandler(
            ch: curl_init(),
            body: true,
            contentType: ContentType::JSON
        );

        $handler->validate();
    }

    /**
     * Assert validate() throws EmptyValueException when body is empty.
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     */
    public function testValidateThrowsWithEmptyJsonContent(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        $handler = new ErrorHandler(
            ch: curl_init(),
            body: '',
            contentType: ContentType::JSON
        );

        $handler->validate();
    }

    /**
     * Assert validate() throws JsonException when body isn't valid JSON.
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     */
    public function testValidateThrowsWithInvalidJsonContent(): void
    {
        $this->expectException(exception: JsonException::class);

        $handler = new ErrorHandler(
            ch: curl_init(),
            body: 'This is not JSON',
            contentType: ContentType::JSON
        );

        $handler->validate();
    }

    /**
     * Assert validate() throws CurlException when body includes a message
     * property.
     *
     * Assert body property on CurlException is set.
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     */
    public function testValidateThrowsWithJsonMessage(): void
    {
        $this->expectException(exception: CurlException::class);

        try {
            $handler = new ErrorHandler(
                ch: curl_init(),
                body: '{"error": "This is a message"}',
                contentType: ContentType::JSON
            );


            $handler->validate();
        } catch (CurlException $e) {
            self::assertSame(
                expected: '{"error": "This is a message"}',
                actual: $e->body,
                message: 'Body mismatch.'
            );

            throw $e;
        }
    }

    /**
     * Assert validate() throws CurlException when HTTP response code is 0.
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ConfigException
     */
    public function testValidateThrowsWithHttpCode0(): void
    {
        $this->expectException(exception: CurlException::class);

        $ch = curl_init(url: 'nowhere.loc/404');

        self::assertNotFalse(condition: $ch);

        curl_exec(handle: $ch);

        $handler = new ErrorHandler(
            ch: $ch,
            body: '{}',
            contentType: ContentType::JSON
        );

        $handler->validate();
    }
}
