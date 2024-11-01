<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Utilities;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * String testing.
 */
class StringsTest extends TestCase
{
    /**
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function testGetObfuscatedString(): void
    {
        $obfuscateFromSecondPosition = Strings::getObfuscatedString(
            string: 'Just a string.',
            startAt: 2
        );
        $obfuscateFromThirdPosition = Strings::getObfuscatedString(
            string: 'Just a string.',
            startAt: 3
        );
        $obfuscateFromFourthPositionEndAtZero = Strings::getObfuscatedString(
            string: 'Just a string.',
            startAt: 4,
            endAt: 0
        );
        // Breaking rules.
        $obfuscateFromFifthAndBreakTheStrLenRules = Strings::getObfuscatedString(
            string: 'Just',
            startAt: 5,
            endAt: 5
        );

        $this->assertEquals(
            expected: 'Ju************.',
            actual: $obfuscateFromSecondPosition
        );
        $this->assertEquals(
            expected: 'Jus************.',
            actual: $obfuscateFromThirdPosition
        );
        $this->assertEquals(
            expected: 'Just************',
            actual: $obfuscateFromFourthPositionEndAtZero
        );
        $this->assertEquals(
            expected: 'Just',
            actual: $obfuscateFromFifthAndBreakTheStrLenRules
        );
    }

    /**
     * Encode string that is not url-safe.
     *
     * @see https://stackoverflow.com/questions/11449577/why-is-base64-encode-adding-a-slash-in-the-result
     */
    public function testBase64urlEncode(): void
    {
        // Real base64 string looks like c3ViamVjdHM/X2Q9MQ
        $this->assertEquals(
            expected: 'c3ViamVjdHM_X2Q9MQ',
            actual: Strings::base64urlEncode(data: 'subjects?_d=1')
        );
    }

    /**
     * Decode string that is not url-safe.
     *
     * @see https://stackoverflow.com/questions/11449577/why-is-base64-encode-adding-a-slash-in-the-result
     */
    public function testBase64urlDecode(): void
    {
        $this->assertEquals(
            expected: 'subjects?_d=1',
            actual: Strings::base64urlDecode(data: 'c3ViamVjdHM_X2Q9MQ')
        );
    }

    /**
     * Test getUuid() method.
     *
     * @throws IllegalValueException
     */
    public function testGetUuid(): void
    {
        $uuid = Strings::getUuid();

        $this->assertTrue(
            condition: (new StringValidation())->isUuid(value: $uuid)
        );
    }
}
