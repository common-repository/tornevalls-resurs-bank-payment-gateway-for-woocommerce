<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Payment\Converter;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Converter\DiscountItem;

/**
 * Test validation methods on DiscountItem model class.
 */
class DiscountItemTest extends TestCase
{
    /**
     * @throws IllegalValueException
     */
    public function testRate(): void
    {
        $model = new DiscountItem(rate: 1.05, amount: 10.1);
        $this->assertInstanceOf(expected: DiscountItem::class, actual: $model);
    }

    /**
     * @throws IllegalValueException
     */
    public function testRateThrowsWithNegativeRate(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new DiscountItem(rate: -99.0, amount: 156670.0);
    }

    /**
     * @throws IllegalValueException
     */
    public function testRateThrowsWithTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new DiscountItem(rate: 25.114, amount: 871230.99);
    }

    /**
     * @throws IllegalValueException
     */
    public function testRateThrowsWithTooLargeValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new DiscountItem(rate: 100.01, amount: 871230.1);
    }

    /**
     * @throws IllegalValueException
     */
    public function testAmount(): void
    {
        $model = new DiscountItem(rate: 25.0, amount: 250.15);
        $this->assertInstanceOf(expected: DiscountItem::class, actual: $model);
    }

    /**
     * @throws IllegalValueException
     */
    public function testAmountThrowsWithNegativeAmount(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new DiscountItem(rate: 25.0, amount: -1232345.0);
    }

    /**
     * @throws IllegalValueException
     */
    public function testAmountThrowsWithTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new DiscountItem(rate: 25.0, amount: 123.144);
    }

    /**
     * @throws IllegalValueException
     */
    public function testAmountThrowsWithTooLargeValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new DiscountItem(rate: 25.0, amount: 99999999999.0);
    }
}
