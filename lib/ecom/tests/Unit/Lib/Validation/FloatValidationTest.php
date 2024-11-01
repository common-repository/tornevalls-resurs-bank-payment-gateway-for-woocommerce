<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Validation;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Lib\Validation\FloatValidation;

/**
 * Test float validation methods.
 */
final class FloatValidationTest extends TestCase
{
    private FloatValidation $floatValidation;

    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        $this->floatValidation = new FloatValidation();

        parent::setUp();
    }

    /**
     * Assert getKey() throws MissingKeyException when the needle does not
     * exist.
     *
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyThrowsWithMissing(): void
    {
        $this->expectException(exception: MissingKeyException::class);
        $this->floatValidation->getKey(data: ['Finland', 'Brown'], key: 'win');
    }

    /**
     * Assert getKey() throws IllegalTypeException when the needle exists but
     * is not a float.
     *
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyThrowsWithIllegalType(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->floatValidation->getKey(data: ['mime' => true], key: 'mime');
    }

    /**
     * Assert getKey() return validated float value.
     *
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyReturnsFloat(): void
    {
        $this->assertSame(
            expected: 10.55,
            actual: $this->floatValidation->getKey(
                data: ['epic' => 10.55],
                key: 'epic'
            )
        );
    }

    /**
     * Assert inRange() throws IllegalValueException when max is less than min.
     *
     * @throws IllegalValueException
     */
    public function testInRangeThrowsIfMaxIsLessThanMin(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->floatValidation->inRange(value: 10.0, min: 10, max: 5);
    }

    /**
     * Assert inRange() throws IllegalValueException when the value is out of
     * range.
     *
     * @throws IllegalValueException
     */
    public function testInRangeThrowsIfValueIsOutOfRange(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->floatValidation->inRange(value: 10.0, min: 0, max: 5);
    }

    /**
     * Assert inRange() returns true when the value is in the given range.
     *
     * @throws IllegalValueException
     */
    public function testInRangeReturnsTrueWhenValueInRange(): void
    {
        $this->assertTrue(
            condition: $this->floatValidation->inRange(
                value: 10.0,
                min: 5,
                max: 10
            )
        );
    }

    /**
     * Assert length() throws IllegalValueException when max is less than min.
     *
     * @throws IllegalValueException
     */
    public function testLengthThrowsIfMaxIsLessThanMin(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->floatValidation->length(value: 10.0, min: 10, max: 5);
    }

    /**
     * Assert length() throws IllegalValueException when given a min value that
     * is negative.
     *
     * @throws IllegalValueException
     */
    public function testLengthThrowsIfMinIsNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->floatValidation->length(value: 10.0, min: -1, max: 5);
    }

    /**
     * Assert length() throws IllegalValueException when the value is out of
     * range.
     *
     * @throws IllegalValueException
     */
    public function testLengthThrowsIfValueIsOutOfRange(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->floatValidation->length(value: 10.1, min: 2, max: 3);
    }

    /**
     * Assert length() returns true when the value is in the given range.
     *
     * @throws IllegalValueException
     */
    public function testLengthReturnsTrueWhenValueInRange(): void
    {
        $this->assertTrue(
            condition: $this->floatValidation->length(
                value: 10.123,
                min: 2,
                max: 5
            )
        );
    }

    /**
     * Assert isPositive() throws IllegalValueException when the value is negative.
     *
     * @throws IllegalValueException
     */
    public function testIsPositiveThrowsOnNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->floatValidation->isPositive(value: -1);
    }

    /**
     * Assert isPositive() returns true when the value is positive.
     *
     * @throws IllegalValueException
     */
    public function testIsPositiveReturnTrue(): void
    {
        $this->assertTrue(
            condition: $this->floatValidation->isPositive(value: 1)
        );
    }
}
