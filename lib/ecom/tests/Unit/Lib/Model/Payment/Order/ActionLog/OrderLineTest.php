<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Payment\Order\ActionLog;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine as OrderLineModel;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\EcomTest\Data\OrderLine;
use stdClass;

use function strlen;

/**
 * Test data integrity of order line entity model.
 */
class OrderLineTest extends TestCase
{
    private OrderLineModel $item;

    private stdClass $data;

    /**
     * @throws JsonException
     * @throws TestException
     */
    protected function setUp(): void
    {
        $this->data = OrderLine::getRandomData();

        parent::setUp();
    }

    /**
     * @param array<string, mixed> $updates
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function convert(
        array $updates = []
    ): void {
        foreach ($updates as $key => $val) {
            $this->data->{$key} = $val;
        }

        $item = DataConverter::stdClassToType(
            object: $this->data,
            type: OrderLineModel::class
        );

        if (!$item instanceof OrderLineModel) {
            throw new TestException(
                message: 'Conversion succeeded but did not return ' .
                    'Order Line instance.'
            );
        }

        $this->item = $item;
    }

    /**
     * Check if the $item property on $this instance has been initiated.
     *
     * @throws ReflectionException
     */
    private function isItemInitialized(): bool
    {
        return (
        new ReflectionProperty(class: $this, property: 'item')
        )->isInitialized(object: $this);
    }

    /**
     * Get an anonymous array with price data for all price related properties
     * in the OrderLine class.
     *
     * @param array $props | Anonymous array of properties on OrderLine class.
     * @param string $illegalProperty | Invert value for specified property (to test illegal values).
     * @throws Exception
     */
    private function getPriceData(
        OrderLineType $type,
        array $props,
        string $illegalProperty = ''
    ): array {
        $result = [
            'type' => $type,
        ];

        foreach ($props as $prop) {
            $price = $this->getRandomPrice();

            // Using negative values for discount lines.
            if ($type === OrderLineType::DISCOUNT) {
                $price = -$price;
            }

            $result[$prop] = $prop === $illegalProperty ? $price * -1 : $price;
        }

        return $result;
    }

    /**
     * Test that supplied price data is accepted by the OrderLine model.
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    private function testAllowedPriceData(
        array $data,
        string $prop,
        string $message
    ): void {
        try {
            unset($this->item);
            $this->convert(updates: $data);
            $this->assertSame(
                expected: $this->item->{$prop},
                actual: $data[$prop]
            );
        } catch (IllegalValueException $e) {
            $this->fail(message: "$message. Exception " . $e->getMessage());
        }
    }

    /**
     * Test that supplied price data is rejected by the OrderLine model.
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    private function testDisallowedPriceData(
        array $data,
        string $message
    ): void {
        // Test that negative value is DISALLOWED for NORMAL.
        try {
            unset($this->item);
            $this->convert(updates: $data);

            // Will only occur if no Exception was thrown.
            $this->fail(message: $message);
        } catch (IllegalValueException) {
            $this->assertFalse(condition: $this->isItemInitialized());
        }
    }

    /**
     * Resolve a random value within the confounds of price property validation.
     *
     * @throws Exception
     */
    private function getRandomPrice(): float
    {
        $int = random_int(min: 1, max: 9999999999);
        $dec = random_int(min: 1, max: 99) / 100;

        return round(num: $int + $dec, precision: 2);
    }

    /**
     * @throws Exception
     */
    private function getRandomString(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[random_int(
                min: 0,
                max: strlen(string: $characters) - 1
            )];
        }

        return $string;
    }

    /**
     * Assert validateDescription() throws IllegalValueException when its
     * length is too long.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws Exception
     */
    public function testValidateDescriptionThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'description' => $this->getRandomString(length: 101),
        ]);
    }

    /**
     * Assert validateReference() throws IllegalValueException when its
     * length is too long.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws Exception
     */
    public function testValidateReferenceThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'reference' => $this->getRandomString(length: 51),
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
            'quantityUnit' => $this->getRandomString(length: 51),
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

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testDescriptionWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->description,
            actual: $this->item->description
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testReferenceWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->reference,
            actual: $this->item->reference
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testTypeWasAssigned(): void
    {
        $this->convert();
        $this->assertNotNull(actual: $this->item->type);
        $this->assertSame(
            expected: $this->data->type,
            actual: $this->item->type->value
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testQuantityUnitWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->quantityUnit,
            actual: $this->item->quantityUnit
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testQuantityWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->quantity,
            actual: $this->item->quantity
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testVatRateWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->vatRate,
            actual: $this->item->vatRate
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testUnitAmountIncludingVatWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->unitAmountIncludingVat,
            actual: $this->item->unitAmountIncludingVat
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testTotalAmountIncludingVatWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->totalAmountIncludingVat,
            actual: $this->item->totalAmountIncludingVat
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testTotalVatAmountWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->totalVatAmount,
            actual: $this->item->totalVatAmount
        );
    }

    /**
     * Assert that price related values on OrderLine instance only accept
     * positive values if the type _is not_ DISCOUNT, and vice versa if it is.
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     * @throws Exception
     */
    public function testAcceptablePriceValues(): void
    {
        $props = [
            'totalAmountIncludingVat',
            'unitAmountIncludingVat',
            'totalVatAmount',
        ];

        foreach ($props as $prop) {
            // Test that positive value is ALLOWED for NORMAL.
            $this->testAllowedPriceData(
                data: $this->getPriceData(
                    type: OrderLineType::NORMAL,
                    props: $props
                ),
                prop: $prop,
                message: "$prop failed with NORMAL type and positive value."
            );

            // Test that negative value is ALLOWED for DISCOUNT.
            $this->testAllowedPriceData(
                data: $this->getPriceData(
                    type: OrderLineType::DISCOUNT,
                    props: $props
                ),
                prop: $prop,
                message: "$prop failed with DISCOUNT type and negative value."
            );
        }
    }
}
