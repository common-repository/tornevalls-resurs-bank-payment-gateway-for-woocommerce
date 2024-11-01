<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Payment\Models;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Order as OrderModel;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\EcomTest\Data\Order;
use stdClass;

use function array_fill;
use function json_decode;
use function json_encode;

/**
 * Test data integrity of order entity model.
 */
class OrderTest extends TestCase
{
    private stdClass $data;

    /**
     * @throws JsonException
     * @throws TestException
     */
    protected function setUp(): void
    {
        $this->data = Order::getData();

        parent::setUp();
    }

    /**
     * @param array<string, mixed> $updates
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    private function convert(
        array $updates = []
    ): void {
        foreach ($updates as $key => $val) {
            $this->data->{$key} = $val;
        }

        $item = DataConverter::stdClassToType(
            object: $this->data,
            type: OrderModel::class
        );

        if (!$item instanceof OrderModel) {
            throw new TestException(
                message: 'Conversion succeeded but did not return Order instance.'
            );
        }
    }

    /**
     * Assert validateOrderLines() throws IllegalValueException when its
     * length is too long.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws JsonException
     */
    public function testValidateOrderLinesThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'orderLines' => json_decode(
                json: json_encode(value: array_fill(
                    start_index: 0,
                    count: 1001,
                    value: new OrderLine(
                        description: 'test',
                        reference: 'test',
                        quantityUnit: 'test',
                        quantity: 20.1,
                        vatRate: 20,
                        unitAmountIncludingVat: 20,
                        totalAmountIncludingVat: 20.1,
                        totalVatAmount: 20.1,
                        type: OrderLineType::NORMAL
                    )
                ), flags: JSON_THROW_ON_ERROR),
                associative: false,
                depth: 512,
                flags: JSON_THROW_ON_ERROR
            ),
        ]);
    }

    /**
     * Assert validateOrderLines() throws IllegalValueException when its
     * length is too short.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateOrderLinesThrowsWhenTooShort(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'orderLines' => [],
        ]);
    }

    /**
     * Assert validateReference() throws IllegalValueException when its
     * length is too long.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateReferenceThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'orderReference' => 'Lorem ipsum dolor sit amet, consectetur ' .
                'adipiscing elit. Pellentesque tempus gravida varius.',
        ]);
    }

    /**
     * Assert validateReference() throws IllegalValueException when its
     * length is too short.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateReferenceThrowsWhenTooShort(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'orderReference' => '',
        ]);
    }

    /**
     * Assert validateReference() throws IllegalValueException when it uses
     * illegal characters.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateReferenceThrowsWhenUsingIllegalChars(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'orderReference' => 'Test!',
        ]);
    }
}
