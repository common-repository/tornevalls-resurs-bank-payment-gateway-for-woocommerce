<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Payment\Models\CreatePayment\Order;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\DataConverter;

/**
 * Test data integrity of order line entity model.
 */
class OrderLineTest extends TestCase
{
    /** @var array<string, mixed> */
    private static array $data = [];

    /**
     * @throws JsonException
     * @throws IllegalValueException
     */
    protected function setUp(): void
    {
        /** @var array<string, mixed> $data */
        $data = json_decode(
            json: json_encode(
                value: new OrderLine(
                    quantity: 1,
                    quantityUnit: 'st',
                    vatRate: 10,
                    totalAmountIncludingVat: 11,
                    description: 'Item',
                    reference: 'I-200',
                    type: OrderLineType::NORMAL,
                    unitAmountIncludingVat: 10,
                    totalVatAmount: 1
                ),
                flags: JSON_THROW_ON_ERROR
            ),
            associative: true,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        self::$data = $data;

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
    ): OrderLine {
        $result = DataConverter::stdClassToType(
            object: (object) array_merge(self::$data, $updates),
            type: OrderLine::class
        );

        if (!$result instanceof OrderLine) {
            throw new TestException(
                message: 'Failed to convert stdClass to PaymentMethod.'
            );
        }

        return $result;
    }

    /**
     * Assert validateDescription() throws IllegalValueException when its
     * length is too long.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateDescriptionThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'description' => 'Lorem ipsum dolor sit amet, consectetur ' .
                'adipiscing.',
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
            'reference' => 'Lorem ipsum dolor sit amet, consectetur ' .
                'adipiscing.',
        ]);
    }

    /**
     * Assert validateQuantityUnit() throws IllegalValueException when its
     * length is too long.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateQuantityUnitThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'quantityUnit' => 'Lorem ipsum dolor sit amet, consectetur ' .
                'adipiscing. ',
        ]);
    }

    /**
     * Assert validateVatRate() throws IllegalValueException when its
     * value is negative.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testVatRateThrowsWhenNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['vatRate' => -10]);
    }

    /**
     * Assert validateVatRate() throws IllegalValueException when its
     * value has more than 2 decimals digits.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testVatRateThrowsWhenItHasTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['vatRate' => 0.999]);
    }

    /**
     * Assert validateVatRate() throws IllegalValueException when its
     * value has more than 2 integer digits.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testVatRateThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['vatRate' => 100]);
    }

    /**
     * Assert validateQuantity() throws IllegalValueException when its
     * value is negative.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testQuantityThrowsWhenNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['quantity' => -10]);
    }

    /**
     * Assert validateQuantity() throws IllegalValueException when its
     * value has more than 2 decimals digits.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testQuantityThrowsWhenItHasTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['quantity' => 0.999]);
    }

    /**
     * Assert validateQuantity() throws IllegalValueException when its
     * value has more than 10 integer digits.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testQuantityThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['quantity' => 99999999999]);
    }

     /**
     * Assert validateUnitAmountIncludingVat() throws IllegalValueException when
     * its value has more than 2 decimals digits.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testUnitAmountIncludingVatThrowsWhenItHasTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['unitAmountIncludingVat' => 0.999]);
    }

    /**
     * Assert validateUnitAmountIncludingVat() throws IllegalValueException when
     * its value has more than 10 integer digits.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testUnitAmountIncludingVatThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['unitAmountIncludingVat' => 99999999999]);
    }

    /**
     * Assert validateTotalAmountIncludingVat() throws IllegalValueException
     * when its value has more than 2 decimals digits.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalAmountIncludingVatThrowsWhenItHasTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['totalAmountIncludingVat' => 0.999]);
    }

    /**
     * Assert validateTotalAmountIncludingVat() throws IllegalValueException
     * when its value has more than 10 integer digits.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalAmountIncludingVatThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['totalAmountIncludingVat' => 99999999999]);
    }

    /**
     * Assert validateTotalVatAmount() throws IllegalValueException when
     * its value has more than 2 decimals digits.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalVatAmountThrowsWhenItHasTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['totalVatAmount' => 0.999]);
    }

    /**
     * Assert validateTotalVatAmount() throws IllegalValueException when
     * its value has more than 10 integer digits.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalVatAmountThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['totalVatAmount' => 99999999999]);
    }
}
