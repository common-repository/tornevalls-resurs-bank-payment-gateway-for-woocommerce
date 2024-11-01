<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Validation;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Lib\Validation\IntValidation;

/**
 * Test integer validation methods.
 */
final class IntValidationTest extends TestCase
{
    private IntValidation $intValidation;

    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        $this->intValidation = new IntValidation();

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
        $this->intValidation->getKey(data: ['Sweden', 'Blue'], key: 'bacon');
    }

    /**
     * Assert getKey() throws IllegalTypeException when the needle exists but
     * is not an integer.
     *
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyThrowsWithIllegalType(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->intValidation->getKey(data: ['epic' => '999'], key: 'epic');
    }

    /**
     * Assert getKey() return validated integer value.
     *
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyReturnsInt(): void
    {
        $this->assertSame(
            expected: 123,
            actual: $this->intValidation->getKey(
                data: ['epic' => 123],
                key: 'epic'
            )
        );
    }

    /**
     * Assert isPositive() throws IllegalValueException when the value is
     * negative.
     *
     * @throws IllegalValueException
     */
    public function testIsPositiveThrowsOnNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->intValidation->isPositive(value: -1);
    }

    /**
     * Assert isPositive() returns true when the value is positive.
     */
    public function testInRangeThrowsWithIllegalValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->intValidation->inRange(value: 100, min: 0, max: 1);
    }

    /**
     * Assert inRange() throws IllegalValueException when the max value is less
     * than the min value.
     *
     * @throws IllegalValueException
     */
    public function testIsPositiveReturnTrue(): void
    {
        $this->assertTrue(
            condition: $this->intValidation->isPositive(value: 1)
        );
    }

    /**
     * Assert isGt() throws IllegalValueException when the value is less than
     * supplied minimum.
     *
     * @throws IllegalValueException
     */
    public function testIsGtThrowsOnLessThan(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->intValidation->isGt(value: 1, min: 2);
    }

    /**
     * Assert isGt() returns true when the value is greater than supplied
     * minimum.
     *
     * @throws IllegalValueException
     */
    public function testIsGtReturnTrue(): void
    {
        $this->assertTrue(
            condition: $this->intValidation->isGt(value: 2, min: 1)
        );
    }

    /**
     * Assert inRange() throws IllegalValueException when the tested integer is
     * out of range.
     */
    public function testInRangeThrowsWithIllegalValueWhenMaxIsInvalid(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->intValidation->inRange(value: 5, min: 3, max: 1);
    }

    /**
     * Asserts that inRange() validates that the tested integer within range.
     *
     * @throws IllegalValueException
     */
    public function testInRangeReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->intValidation->inRange(
                value: 5,
                min: 0,
                max: 10
            )
        );
    }
}
