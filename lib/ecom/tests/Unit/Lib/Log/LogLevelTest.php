<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Log;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Log\LogLevel;

/**
 * Verifies that the LogLevel enum works as intended.
 */
class LogLevelTest extends TestCase
{
    /**
     * Assert that only configured log level or higher show as loggable when there is a Config instance
     *
     * @throws ConfigException
     */
    public function testLoggableWithConfigInstance(): void
    {
        Config::setup(logLevel: LogLevel::WARNING);

        self::assertFalse(
            condition: LogLevel::loggable(level: LogLevel::DEBUG)
        );
        self::assertFalse(
            condition: LogLevel::loggable(level: LogLevel::INFO)
        );
        self::assertTrue(
            condition: LogLevel::loggable(level: LogLevel::WARNING)
        );
        self::assertTrue(
            condition: LogLevel::loggable(level: LogLevel::ERROR)
        );
        self::assertTrue(
            condition: LogLevel::loggable(level: LogLevel::EXCEPTION)
        );
    }

    /**
     * Assert that all log levels show as loggable when there is no Config instance
     *
     * @throws ConfigException
     */
    public function testLoggableWithoutConfigInstance(): void
    {
        if (Config::hasInstance()) {
            Config::unsetInstance();
        }

        foreach (LogLevel::cases() as $logLevel) {
            self::assertTrue(
                condition: LogLevel::loggable(level: $logLevel)
            );
        }
    }
}
