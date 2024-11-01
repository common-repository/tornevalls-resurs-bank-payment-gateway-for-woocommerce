<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Payment\Models\CreatePaymentRequest;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options;

/**
 * Unit tests for order options
 */
class OptionsTest extends TestCase
{
    /**
     * Assert that an IllegalValueException is thrown when attempting to set a TTL which is too long.
     *
     * @throws IllegalValueException
     */
    public function testTooLongTimeToLive(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Options(timeToLiveInMinutes: 43201);
    }

    /**
     * Assert that an IllegalValueException is thrown when attempting to set a TTL which is negative
     *
     * @throws IllegalValueException
     */
    public function testNegativeTimeToLive(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Options(timeToLiveInMinutes: -5);
    }

    /**
     * Assert that an IllegalValueException is thrown when attempting to set a TTL which is zero
     *
     * @throws IllegalValueException
     */
    public function testZeroTimeToLive(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Options(timeToLiveInMinutes: 0);
    }

    /**
     * Assert that an IllegalValueException is thrown if attempting to create an Options object with both
     * handleFrozenPayments and automaticCapture set to true.
     */
    public function testAutomaticCaptureValidation(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Options(handleFrozenPayments: true, automaticCapture: true);
    }
}
