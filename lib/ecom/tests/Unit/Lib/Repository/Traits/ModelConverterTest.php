<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Repository\Traits;

use DateTime;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Repository\Traits\ModelConverter;
use Resursbank\EcomTest\Data\Models\Instrument;
use Resursbank\EcomTest\Data\Models\InstrumentCollection;
use stdClass;

/**
 * Verifies business logic of ModelConverter trait.
 */
final class ModelConverterTest extends TestCase
{
    use ModelConverter;

    /** @var array<array<string, mixed>> */
    private static array $data = [
        [
            'id' => 1,
            'name' => 'Guitar',
        ],
        [
            'id' => 2,
            'name' => 'Piano',
        ],
        [
            'id' => 3,
            'name' => 'Violin',
        ],
    ];

    /**
     * Assert validateModel() throws InvalidArgumentException when supplied a
     * value which is not a class.
     *
     * @throws IllegalTypeException
     */
    public function testValidateModelThrowsWithoutClass(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        /* @phpstan-ignore-next-line */
        $this->validateModel(model: 'Nada');
    }

    /**
     * Assert validateModel() throws IllegalTypeException when supplied a class
     * that is not a subclass of Model.
     *
     * @throws IllegalTypeException
     */
    public function testValidateThrowsWithoutModelClass(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->validateModel(model: DateTime::class);
    }

    /**
     * Assert convertToModel() throws InvalidArgumentException when supplied a
     * model class that does not exist.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testConvertToModelThrowsWithoutClass(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        /* @phpstan-ignore-next-line */
        $this->convertToModel(data: new stdClass(), model: 'Nope');
    }

    /**
     * Assert convertToModel() throws InvalidArgumentException when supplied a
     * model class that does not exist.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testConvertToModelThrowsWithoutModelClass(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->convertToModel(data: new stdClass(), model: DateTime::class);
    }

    /**
     * Assert convertToModel() converts JSON to request Model instance.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testConvertToModelConvertsJsonModel(): void
    {
        $result = $this->convertToModel(
            data: json_encode(
                value: self::$data[0],
                flags: JSON_THROW_ON_ERROR
            ),
            model: Instrument::class
        );

        $this->assertInstanceOf(expected: Instrument::class, actual: $result);
    }

    /**
     * Assert convertToModel() converts JSON to request Model instance.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testConvertToModelConvertsJsonArray(): void
    {
        $result = $this->convertToModel(
            data: json_encode(
                value: self::$data,
                flags: JSON_THROW_ON_ERROR
            ),
            model: Instrument::class
        );

        $this->assertInstanceOf(
            expected: InstrumentCollection::class,
            actual: $result
        );
    }

    /**
     * Assert convertToModel() converts stdClass instance to Model instance.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testConvertToModelConvertsStdclass(): void
    {
        $result = $this->convertToModel(
            data: json_encode(
                value: self::$data[1],
                flags: JSON_THROW_ON_ERROR
            ),
            model: Instrument::class
        );

        $this->assertInstanceOf(expected: Instrument::class, actual: $result);
    }

    /**
     * Assert convertToModel() converts array of stdClass instances to Model
     * instances.
     *
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function testConvertToModelConvertsStdclassArray(): void
    {
        $result = $this->convertToModel(
            data: json_encode(value: self::$data, flags: JSON_THROW_ON_ERROR),
            model: Instrument::class
        );

        $this->assertInstanceOf(
            expected: InstrumentCollection::class,
            actual: $result
        );
    }
}
