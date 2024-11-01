<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Collection;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\CollectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Throwable;

use function get_class;

/**
 * Verifies that the Collection class works as intended.
 */
final class CollectionTest extends TestCase
{
    /** @var array<string> */
    private array $data;

    /**
     * Set up data variable
     */
    protected function setUp(): void
    {
        $this->data = [
            'foo',
            'bar',
            'baz',
            'baf',
        ];

        parent::setUp();
    }

    /**
     * Verify that creation of Collection works
     *
     * @throws IllegalTypeException
     */
    public function testCreateCollection(): void
    {
        $collection = new Collection(data: $this->data, type: 'string');

        $this::assertSame(
            expected: Collection::class,
            actual: $collection::class
        );
    }

    /**
     * Verify that type verification works
     */
    public function testCollectionTypeVerification(): void
    {
        $data = [
            'foo',
            42,
            'bar',
        ];

        $className = false;

        try {
            new Collection(data: $data, type: 'string');
        } catch (Throwable $e) {
            $className = get_class(object: $e);
        }

        $this::assertSame(
            expected: IllegalTypeException::class,
            actual: $className
        );
    }

    /**
     * Verify that it's impossible to add an item of the wrong type to a collection
     *
     * @throws IllegalTypeException
     */
    public function testAddWrongTypeData(): void
    {
        $collection = new Collection(data: $this->data);

        $className = false;

        try {
            $collection[] = 42;
        } catch (Throwable $e) {
            $className = get_class(object: $e);
        }

        $this::assertSame(
            expected: IllegalTypeException::class,
            actual: $className
        );
        $this::assertSame(
            expected: gettype(value: $this->data[0]),
            actual: $collection->getType()
        );
    }

    /**
     * Verify that the toArray method works
     *
     * @throws IllegalTypeException
     */
    public function testToArray(): void
    {
        $collection = new Collection(data: $this->data);
        $this::assertSame(
            expected: $this->data,
            actual: $collection->toArray()
        );
    }

    /**
     * Verify that type determination called in the Collection constructor works
     *
     * @throws IllegalTypeException
     */
    public function testTypeDetermination(): void
    {
        $collection = new Collection(data: $this->data);
        $type = gettype($this->data[0]);

        $this::assertSame(
            expected: $type,
            actual: $collection->getType()
        );
    }

    /**
     * Verify that the count method works
     *
     * @throws IllegalTypeException
     */
    public function testCount(): void
    {
        $collection = new Collection(data: $this->data);

        $this::assertSame(
            expected: count($this->data),
            actual: $collection->count()
        );
    }

    /**
     * Verify that the offsetSet method works
     *
     * @throws IllegalTypeException
     */
    public function testOffsetSet(): void
    {
        $data = ['foo'];

        $collection = new Collection(data: $data);
        $collection[1] = 'bar';

        $this::assertSame(expected: 'bar', actual: $collection[1]);
    }

    /**
     * Verify that the offsetExists method works
     *
     * @throws IllegalTypeException
     */
    public function testOffsetExists(): void
    {
        $collection = new Collection(data: $this->data);

        $this::assertTrue(condition: $collection->offsetExists(offset: 1));
    }

    /**
     * Verify that the offsetUnset method works
     *
     * @throws IllegalTypeException
     */
    public function testOffsetUnset(): void
    {
        $collection = new Collection(data: $this->data);
        unset($collection[1]);

        $this::assertEmpty(actual: $collection[1]);
    }

    /**
     * Verify that the offsetGet method works
     *
     * @throws IllegalTypeException
     */
    public function testOffsetGet(): void
    {
        $collection = new Collection(data: $this->data);

        $this::assertSame(
            expected: $this->data[1],
            actual: $collection->offsetGet(offset: 1)
        );
    }

    /**
     * Verify that the rewind method works
     *
     * @throws IllegalTypeException
     */
    public function testRewind(): void
    {
        $collection = new Collection(data: $this->data);
        $collection->next();
        $collection->next();
        $collection->rewind();

        $this::assertSame(
            expected: 0,
            actual: $collection->key()
        );
    }

    /**
     * Verify that the current method works
     *
     * @throws IllegalTypeException
     * @throws CollectionException
     */
    public function testCurrent(): void
    {
        $collection = new Collection(data: $this->data);
        $collection->next();

        $this::assertSame(
            expected: $this->data[1],
            actual: $collection->current()
        );
    }

    /**
     * Verify that the key method works
     *
     * @throws IllegalTypeException
     */
    public function testKey(): void
    {
        $collection = new Collection(data: $this->data);
        $collection->next();

        $this::assertSame(
            expected: 1,
            actual: $collection->key()
        );
    }

    /**
     * Verify that the next method works
     *
     * @throws IllegalTypeException
     */
    public function testNext(): void
    {
        $collection = new Collection(data: $this->data);
        $originalKey = $collection->key();
        $collection->next();

        $this::assertSame(expected: 0, actual: $originalKey);
        $this::assertSame(
            expected: 1,
            actual: $collection->key()
        );
    }

    /**
     * Verify that the valid method works
     *
     * @throws IllegalTypeException
     */
    public function testValid(): void
    {
        $collection = new Collection(data: $this->data);
        $shouldBeValid = $collection->valid();
        $maxIndex = count(value: $this->data) - 1;

        for ($i = 0; $i <= $maxIndex; $i++) {
            $collection->next();
        }

        $shouldBeInvalid = $collection->valid();

        $this::assertTrue(condition: $shouldBeValid);
        $this::assertNotTrue(condition: $shouldBeInvalid);
    }
}
