<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Network\Curl;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Network\Curl\ErrorTranslator;

/**
 * Basic tests of ErrorTranslator functionality
 */
class ErrorTranslatorTest extends TestCase
{
    /**
     * Set up ECom before attempting to run any tests.
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: new NoneLogger(),
            cache: new None()
        );
    }

    /**
     * Assert that a simple error string translation works.
     *
     * @throws ConfigException
     */
    public function testTranslation(): void
    {
        $input = 'customer.mobilePhone is not valid';

        $this->assertEquals(
            expected: 'Mobile phone is not valid',
            actual: ErrorTranslator::get(errorMessage: $input)
        );
    }
}
