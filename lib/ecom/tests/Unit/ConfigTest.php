<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\FormatException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Locale\Location;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LogLevel;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Log\StdoutLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Basic;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;

/**
 * Tests Config class functionality
 *
 * @todo Improve test coverage.
 */
class ConfigTest extends TestCase
{
    /**
     * Assert that Config::$instance is properly set up when setup() is called with no parameters
     *
     * @throws ConfigException
     */
    public function testSetupWithoutParameters(): void
    {
        Config::setup();

        self::assertInstanceOf(
            expected: NoneLogger::class,
            actual: Config::getLogger()
        );
        self::assertInstanceOf(
            expected: None::class,
            actual: Config::getCache()
        );
        self::assertNull(actual: Config::getBasicAuth());
        self::assertNull(actual: Config::getJwtAuth());
        self::assertEquals(
            expected: LogLevel::INFO,
            actual: Config::getLogLevel()
        );
        self::assertEmpty(actual: Config::getUserAgent());
        self::assertFalse(condition: Config::isProduction());
        self::assertEmpty(actual: Config::getProxy());
        self::assertEquals(
            expected: 0,
            actual: Config::getProxyType()
        );
        self::assertEquals(
            expected: 0,
            actual: Config::getTimeout()
        );
        self::assertEquals(
            expected: Language::en,
            actual: Config::getLanguage()
        );
        self::assertEquals(
            expected: Location::SE,
            actual: Config::getLocation()
        );
    }

    /**
     * Assert that Config::$instance is properly set up when setup() is called with parameters
     *
     * @throws EmptyValueException
     * @throws ConfigException
     */
    public function testSetupWithParameters(): void
    {
        Config::setup(
            logger: new StdoutLogger(),
            cache: new None(),
            basicAuth: new Basic(
                username: $_ENV['BASIC_AUTH_USERNAME'],
                password: $_ENV['BASIC_AUTH_PASSWORD']
            ),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            logLevel: LogLevel::DEBUG,
            userAgent: 'Foo',
            timeout: 42,
            language: Language::sv
        );

        self::assertInstanceOf(
            expected: StdoutLogger::class,
            actual: Config::getLogger()
        );
        self::assertInstanceOf(
            expected: None::class,
            actual: Config::getCache()
        );
        self::assertInstanceOf(
            expected: Basic::class,
            actual: Config::getBasicAuth()
        );
        self::assertInstanceOf(
            expected: Jwt::class,
            actual: Config::getJwtAuth()
        );
        self::assertEquals(
            expected: LogLevel::DEBUG,
            actual: Config::getLogLevel()
        );
        self::assertEquals(
            expected: 'Foo',
            actual: Config::getUserAgent()
        );
        self::assertFalse(condition: Config::isProduction());
        self::assertEmpty(actual: Config::getProxy());
        self::assertEquals(
            expected: 0,
            actual: Config::getProxyType()
        );
        self::assertEquals(
            expected: 42,
            actual: Config::getTimeout()
        );
        self::assertEquals(
            expected: Language::sv,
            actual: Config::getLanguage()
        );
    }

    /**
     * Verifies that the hasBasicAuth method behaves as expected
     */
    public function testHasBasicAuth(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        self::assertEquals(
            expected: false,
            actual: Config::hasBasicAuth()
        );

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: $this->createMock(originalClassName: Basic::class)
        );
        self::assertEquals(
            expected: true,
            actual: Config::hasBasicAuth()
        );
    }

    /**
     * Verifies that the hasJwtAuth method behaves as expected
     */
    public function testHasJwtAuth(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        self::assertEquals(
            expected: false,
            actual: Config::hasJwtAuth()
        );

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            jwtAuth: $this->createMock(originalClassName: Jwt::class)
        );
        self::assertEquals(
            expected: true,
            actual: Config::hasJwtAuth()
        );
    }

    /**
     * Assert that a FormatException is thrown and not caught along the way when attempting to run Config::setup with an
     * incorrectly formatted path
     */
    public function testSetupWithFileLoggerAndTrailingSlash(): void
    {
        $this->expectException(exception: FormatException::class);

        Config::setup(
            logger: new FileLogger(
                path: '/tmp/'
            )
        );
    }
}
