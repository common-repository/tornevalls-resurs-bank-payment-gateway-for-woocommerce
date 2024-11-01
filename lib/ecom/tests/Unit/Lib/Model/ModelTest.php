<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model;

use PHPUnit\Framework\TestCase;
use Resursbank\EcomTest\Data\Models\ArrayPropertyDummy;
use Resursbank\EcomTest\Data\Models\ObjectPropertyDummy;
use Resursbank\EcomTest\Data\Models\SimpleDummy;

/**
 * Verifies that the Model class works as intended.
 */
final class ModelTest extends TestCase
{
    /**
     * Verify that simple un-nested conversion to array works
     */
    public function testSimpleToArray(): void
    {
        $object = new SimpleDummy(number: 42, message: 'Foo');

        $expected = [
            'number' => 42,
            'message' => 'Foo',
        ];

        $this::assertSame(
            expected:$expected,
            actual: $object->toArray()
        );
    }

    /**
     * Verify that conversion to array works when object has object properties
     */
    public function testWithObjectPropertiesToArray(): void
    {
        $object = new ObjectPropertyDummy(
            object: new SimpleDummy(
                number: 42,
                message: 'Foo'
            ),
            message: 'bar'
        );

        $expected = [
            'object' => [
                'number' => 42,
                'message' => 'Foo',
            ],
            'message' => 'bar',
        ];

        $this::assertSame(
            expected: $expected,
            actual: $object->toArray()
        );
    }

    /**
     * Verify that conversion to array works when object has array properties
     */
    public function testWithArrayPropertiesToArray(): void
    {
        $object = new ArrayPropertyDummy(
            array: [
                'object' => new SimpleDummy(
                    number: 127,
                    message: 'Foo'
                ),
                'number' => 42,
            ],
            message: 'bar'
        );

        $expected = [
            'array' => [
                'object' => [
                    'number' => 127,
                    'message' => 'Foo',
                ],
                'number' => 42,
            ],
            'message' => 'bar',
        ];

        $this::assertEquals(
            expected: $expected,
            actual: $object->toArray(full: true)
        );
    }
}
