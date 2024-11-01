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
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use stdClass;

/**
 * Test array validation methods.
 */
final class ArrayValidationTest extends TestCase
{
    private ArrayValidation $arrayValidation;

    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        $this->arrayValidation = new ArrayValidation();

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
        $this->arrayValidation->getKey(data: ['Norway', 'Red'], key: 'beacon');
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
        $this->arrayValidation->getKey(data: ['none' => true], key: 'none');
    }

    /**
     * Assert getKey() return validated array value.
     *
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyReturnsArray(): void
    {
        $this->assertSame(
            expected: ['test', 'a', 'test'],
            actual: $this->arrayValidation->getKey(
                data: ['epic' => ['test', 'a', 'test']],
                key: 'epic'
            )
        );
    }

    /**
     * Assert isSequential() throws IllegalValueException if supplied array is
     * not sequential.
     */
    public function testIsSequentialThrowsWithAssoc(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->arrayValidation->isSequential(data: ['string' => 'index']);
    }

    /**
     * Assert isSequential() returns TRUE.
     *
     * @throws IllegalValueException
     */
    public function testIsSequentialReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->arrayValidation->isSequential(
                data: ['1', '2', '3']
            )
        );
    }

    /**
     * Assert isAssoc() throws IllegalValueException if supplied array is not
     * associative.
     */
    public function testIsAssocThrowsWithAssoc(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->arrayValidation->isAssoc(data: ['test', 'asd', 'mb']);
    }

    /**
     * Assert isAssoc() returns TRUE.
     *
     * @throws IllegalValueException
     */
    public function testIsAssocReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->arrayValidation->isAssoc(
                data: ['mb' => 'string', 'tb' => 'honest']
            )
        );
    }

    /**
     * Assert isMultiDimensional() throws IllegalTypeException when passed a
     * value containing an array with inconsistent depth.
     *
     * @throws IllegalTypeException
     */
    public function testIsMultiDimensionalThrowsWithoutArrayAtDepth(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->arrayValidation->isMultiDimensional(
            data: [[[]], [[]], ['ERROR']],
            depth: 2
        );
    }

    /**
     * Assert isMultiDimensional() throws IllegalTypeException when passed a
     * one dimensional array with a greater depth expected.
     *
     * @throws IllegalTypeException
     */
    public function testIsMultiDimensionalThrowsWithSingleDimension(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->arrayValidation->isMultiDimensional(
            data: ['test', 'test2'],
            depth: 1
        );
    }

    /**
     * Assert isMultiDimensional() throws IllegalTypeException when passed an
     * accurate structure but with a consistently low depth.
     *
     * @throws IllegalTypeException
     */
    public function testIsMultiDimensionalThrowsWithAssoc(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->arrayValidation->isMultiDimensional(
            data: [['what' => 'dance'], ['lambada' => 'forbidden']],
            depth: 2
        );
    }

    /**
     * Assert isMultiDimensional() returns TRUE when passed a multidimensional
     * array with a depth of one.
     *
     * @throws IllegalTypeException
     */
    public function testIsMultiDimensionalReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->arrayValidation->isMultiDimensional(
                data: [[], ['I'], ['am'], ['Array']],
                depth: 1
            )
        );
    }

    /**
     * Assert isMultiDimensional() returns TRUE when passed a multidimensional
     * array with a depth greater than one.
     *
     * @throws IllegalTypeException
     */
    public function testIsMultiDimensionalReturnsTrueAtDepth(): void
    {
        $this->assertTrue(
            condition: $this->arrayValidation->isMultiDimensional(
                data: [
                    [[
                        ['water', 'splashes', 'here'],
                        ['I', 'drank', 'many', 'beers', 'standing'],
                        ['then', 'I', 'fell', 'over'],
                    ]],
                    [[
                        ['no', 'more'],
                        ['inline'],
                    ]],
                    [[
                        ['epics'],
                    ]],
                ],
                depth: 3
            )
        );
    }

    /**
     * Assert isMultiDimensional() returns TRUE when passed a multidimensional
     * array with a partial depth greater than the depth check.
     *
     * @throws IllegalTypeException
     */
    public function testIsMultiDimensionalReturnsTrueWithGreaterDepth(): void
    {
        $this->assertTrue(
            condition: $this->arrayValidation->isMultiDimensional(
                data: [[], ['test'], [['dynamite', 'clear']]],
                depth: 1
            )
        );
    }

    /**
     * Assert isStdClassCollection() throws IllegalTypeException if the supplied
     * array contains an element that is not an instance of stdClass.
     */
    public function testIsStdClassCollectionThrowsWithString(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->arrayValidation->isStdClassCollection(
            data: ['test' => new stdClass(), 'test2' => 'asd']
        );
    }

    /**
     * Assert isStdClassCollection() returns TRUE.
     *
     * @throws IllegalTypeException
     */
    public function testIsStdClassCollectionReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->arrayValidation->isStdClassCollection(data: [
                'test' => new stdClass(),
                'test2' => new stdClass(),
            ])
        );
    }

    /**
     * Assert allowedKeys() throws IllegalValueException if the supplied array
     * which contains a key that is not in the supplied array of allowed keys.
     *
     * @throws IllegalValueException
     */
    public function testAllowedKeysThrowsWithIllegal(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->arrayValidation->allowedKeys(
            data: ['test' => 'test', 'test2' => 'test2', 'test3' => 'test3'],
            allowed: ['test', 'test2']
        );
    }

    /**
     * Assert that allowedKeys() returns TRUE.
     *
     * @throws IllegalValueException
     */
    public function testAllowedKeysReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->arrayValidation->allowedKeys(
                data: ['test' => 'test', 'test2' => 'test2'],
                allowed: ['test', 'test2']
            )
        );
    }

    /**
     * Test that length() throws IllegalValueException when given a max value
     * that is lower than the given min value.
     *
     * @throws IllegalValueException
     */
    public function testLengthThrowsIfMaxIsLessThanMin(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->arrayValidation->length(data: [], min: 10, max: 5);
    }

    /**
     * Test that length() throws IllegalValueException when given a min value
     * that is negative.
     *
     * @throws IllegalValueException
     */
    public function testLengthThrowsIfMinIsNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->arrayValidation->length(data: [], min: -1, max: 5);
    }

    /**
     * Test that length() throws IllegalValueException when the given array has
     * a length that does not fit into the specified min and max parameters.
     *
     * @throws IllegalValueException
     */
    public function testLengthThrowsIfValueHasInvalidLength(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->arrayValidation->length(data: [1, 2, 3], min: 0, max: 2);
    }
}
