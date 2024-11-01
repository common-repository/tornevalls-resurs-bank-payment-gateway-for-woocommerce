<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network;

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
use Resursbank\Ecom\Lib\Model\Network\Response;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\Generic;
use stdClass;

use function is_string;

/**
 * This class will test curl methods.
 */
class CurlTest extends TestCase
{
    private function getRequestBodyObject(
        Response $response
    ): stdClass {
        if (!($response->body instanceof stdClass)) {
            $this->fail(message: 'Response body is not an object.');
        }

        return $response->body;
    }

    private function validateRequestMethod(
        Response $response,
        string $expected
    ): void {
        $body = $this->getRequestBodyObject(response: $response);

        if (!isset($body->REQUEST_METHOD)) {
            $this->fail(message: 'No REQUEST_METHOD found in response body.');
        }

        $this->assertSame(expected: $expected, actual: $body->REQUEST_METHOD);
    }

    private function validateUserAgent(
        Response $response,
        string $startsWith
    ): void {
        $body = $this->getRequestBodyObject(response: $response);

        if (!isset($body->HTTP_USER_AGENT)) {
            $this->fail(message: 'No HTTP_USER_AGENT found in response body.');
        }

        if (!is_string(value: $body->HTTP_USER_AGENT)) {
            $this->fail(
                message: 'HTTP_USER_AGENT in response body is not a string.'
            );
        }

        $this->assertTrue(
            condition: str_starts_with(
                haystack: $body->HTTP_USER_AGENT,
                needle: $startsWith
            )
        );
    }

    private function getInput(
        Response $response
    ): string {
        $body = $this->getRequestBodyObject(response: $response);

        if (!isset($body->input)) {
            $this->fail(message: 'No input found in response body.');
        }

        if (!is_string(value: $body->input)) {
            $this->fail(message: 'input in response body is not a string.');
        }

        return $body->input;
    }

    /**
     * Verify that Basic auth properties are set when creating a Basic auth instance
     *
     * @throws EmptyValueException
     * @throws ConfigException
     */
    public function testNormalAuthentication(): void
    {
        $username = 'user';
        $password = 'password';

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: new Basic(username: $username, password: $password)
        );

        $auth = Config::getBasicAuth();

        if ($auth === null) {
            $this->fail(message: 'Basic auth is not set.');
        }

        $this::assertSame(expected: $username, actual: $auth->username);

        $this::assertSame(expected: $password, actual: $auth->password);
    }

    /**
     * Test to make sure that remote requests really works.
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     */
    public function testRealGetRequest(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            userAgent: self::class
        );

        $curl = new Curl(
            url: 'https://ipv4.netcurl.org',
            requestMethod: RequestMethod::GET,
            contentType: ContentType::URL,
            authType: AuthType::NONE,
            responseContentType: ContentType::JSON
        );
        $response = $curl->exec();

        $this->validateUserAgent(response: $response, startsWith: self::class);
        $this->validateRequestMethod(response: $response, expected: 'GET');

        $this->assertSame(expected: 200, actual: $response->code);
    }

    /**
     * Test to make sure that remote requests really works.
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testRealGetRequestWithCustomUserAgent(): void
    {
        $expectRemoteVersion = 'EComTest-Custom-' .
            (new Generic())->getVersionByClassDoc(className: self::class);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            userAgent: $expectRemoteVersion
        );

        $curl = new Curl(
            url: 'https://ipv4.netcurl.org',
            requestMethod: RequestMethod::GET,
            contentType: ContentType::URL,
            authType: AuthType::NONE,
            responseContentType: ContentType::JSON
        );
        $response = $curl->exec();

        $this->validateUserAgent(
            response: $response,
            startsWith: $expectRemoteVersion
        );
        $this->validateRequestMethod(response: $response, expected: 'GET');

        $this->assertSame(expected: 200, actual: $response->code);
    }

    /**
     * Test to make sure that remote requests really works.
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
    public function testRealPostRequest(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $payload = new stdClass();
        $payload->customRow = 'Present';
        $response = Curl::post(
            url: 'https://ipv4.netcurl.org',
            payload: (array)$payload,
            authType: AuthType::NONE
        );

        $this->validateRequestMethod(response: $response, expected: 'POST');

        $this->assertEquals(
            expected: $payload,
            actual: json_decode(
                json: $this->getInput(response: $response),
                associative: false,
                depth: 32,
                flags: JSON_THROW_ON_ERROR
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
    public function testRealPutRequest(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $payload = new stdClass();
        $payload->customRow = 'Present';
        $response = Curl::put(
            url: 'https://ipv4.netcurl.org',
            payload: (array)$payload,
            authType: AuthType::NONE
        );

        $this->validateRequestMethod(response: $response, expected: 'PUT');

        $this->assertEquals(
            expected: $payload,
            actual: json_decode(
                json: $this->getInput(response: $response),
                associative: false,
                depth: 16,
                flags: JSON_THROW_ON_ERROR
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
    public function testRealDeleteRequest(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );

        $response = Curl::delete(
            url: 'https://ipv4.netcurl.org',
            authType: AuthType::NONE
        );

        $this->validateRequestMethod(response: $response, expected: 'DELETE');

        $this->assertSame(expected: 200, actual: $response->code);
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
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @noinspection SpellCheckingInspection
     */
    public function testTimeout(): void
    {
        //$this->expectExceptionCode(code: 28);
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            timeout: 2
        );

        $timeoutUrl = 'https://timeout.netcurl.org';

        // We need to move those features "in house" at some point (like timeout.resurs.com).
        $curl = new Curl(
            url: $timeoutUrl,
            requestMethod: RequestMethod::GET,
            authType: AuthType::NONE
        );

        try {
            // Default for requests to "timeout.netcurl.org" is that it responds after a timeout of 10 seconds.
            $curl->exec();
        } catch (CurlException $e) {
            if ($e->getCode() !== 28) {
                static::markTestSkipped(
                    message: sprintf(
                        'Problems occured with %s (error %s: %s).',
                        $timeoutUrl,
                        $e->getCode(),
                        $e->getMessage()
                    )
                );
            } else {
                static::assertSame(expected: 28, actual: $e->getCode());
            }
        }
    }

    /**
     * Verify that CurlException for 404 pages has code set to 404
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @throws IllegalValueException
     * @throws ConfigException
     */
    public function testFileNotFound(): void
    {
        $this->expectException(exception: CurlException::class);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );

        try {
            Curl::get(
                url: 'https://ipv4.netcurl.org/http.php?code=404',
                authType: AuthType::NONE
            );
        } catch (CurlException $e) {
            self::assertSame(expected: 404, actual: $e->httpCode);

            throw $e;
        }
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
    public function testPermissionDenied(): void
    {
        $this->expectException(exception: CurlException::class);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );

        try {
            Curl::get(
                url: 'https://ipv4.netcurl.org/http.php?code=403',
                authType: AuthType::NONE
            );
        } catch (CurlException $e) {
            self::assertSame(expected: 403, actual: $e->httpCode);

            throw $e;
        }
    }
}
