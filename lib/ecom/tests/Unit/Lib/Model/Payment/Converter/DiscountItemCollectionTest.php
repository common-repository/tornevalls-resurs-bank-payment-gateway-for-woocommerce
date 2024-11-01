<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Payment\Converter;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Converter\DiscountItem;
use Resursbank\Ecom\Lib\Model\Payment\Converter\DiscountItemCollection;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;

/**
 * Test DiscountItem model collection.
 */
class DiscountItemCollectionTest extends TestCase
{
    private DiscountItemCollection $collection;
    private DiscountItem $rate6;
    private DiscountItem $rate12;
    private DiscountItem $rate25;

    /**
     * Setup test data.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->rate6 = new DiscountItem(
            rate: 6.0,
            amount: $this->getRandomPrice()
        );

        $this->rate12 = new DiscountItem(
            rate: 12.0,
            amount: $this->getRandomPrice()
        );

        $this->rate25 = new DiscountItem(
            rate: 25.0,
            amount: $this->getRandomPrice()
        );

        $this->collection = new DiscountItemCollection(data: [
            $this->rate6,
            $this->rate12,
            $this->rate25,
        ]);

        Config::setup();

        parent::setUp();
    }

    /**
     * Resolve a random value within the confounds of price property validation.
     *
     * @throws Exception
     */
    private function getRandomPrice(): float
    {
        $int = random_int(min: 1, max: 9999999999);
        $dec = random_int(min: 1, max: 9999999999) / 100;

        return round(num: $int + $dec, precision: 2);
    }

    /**
     * Assert getRate() method returns expected result for various input data.
     */
    public function testGetRate(): void
    {
        $this->assertNull(actual: $this->collection->getByRate(rate: 12.1));

        $rate = $this->collection->getByRate(rate: 25.0);

        $this->assertInstanceOf(expected: DiscountItem::class, actual: $rate);
        $this->assertSame(
            expected: $this->rate25->amount,
            actual: $rate->amount
        );

        $rate2 = $this->collection->getByRate(rate: 6.0);

        $this->assertInstanceOf(expected: DiscountItem::class, actual: $rate2);
        $this->assertSame(
            expected: $this->rate6->amount,
            actual: $rate2->amount
        );

        $this->assertNotSame(expected: $rate->amount, actual: $rate2->amount);
        $this->assertNotSame(expected: $rate->rate, actual: $rate2->rate);
    }

    /**
     * Assert we can add a new rate through addRateData() method.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testAddNewRate(): void
    {
        $price = $this->getRandomPrice();
        $rate = $this->collection->addRateData(rate: 3.0, amount: $price);

        $this->assertSame(expected: $price, actual: $rate->amount);
    }

    /**
     * Assert that when we call addRateData() method and specify an existing
     * rate value our amount will be appended to that DiscountItem and leave all
     * other collection entries unaffected.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testAppendRateAmount(): void
    {
        $price = $this->getRandomPrice();
        $total = round(num: $this->rate25->amount + $price, precision: 2);
        $rate = $this->collection->addRateData(rate: 25.0, amount: $price);

        $this->assertSame(expected: $total, actual: $rate->amount);
        $this->assertSame(
            expected: $this->rate6,
            actual: $this->collection->getByRate(rate: 6.0)
        );
        $this->assertSame(
            expected: $this->rate12,
            actual: $this->collection->getByRate(rate: 12.0)
        );
    }

    /**
     * Test that DiscountItemCollection can be converted to OrderLineCollection.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     */
    public function testGetOrderLines(): void
    {
        $orderLines = $this->collection->getOrderLines()->toArray();

        $this->assertNotEmpty(actual: $orderLines);

        foreach ([$this->rate6, $this->rate12, $this->rate25] as $rateObj) {
            $items = array_filter(
                array: $orderLines,
                callback: static fn (OrderLine $item) => $item->vatRate === $rateObj->rate
            );

            $this->assertCount(
                expectedCount: 1,
                haystack: $items,
                message: "Missing OrderLine object for $rateObj->rate"
            );

            $item = reset(array: $items);

            $this->assertInstanceOf(
                expected: OrderLine::class,
                actual: $item,
                message: "Resolved object for $rateObj->rate is not an OrderLine."
            );
            $this->assertSame(
                expected: -$rateObj->amount,
                actual: $item->totalAmountIncludingVat,
                message: "Resolved object for $rateObj->rate does not match rate."
            );
            $this->assertSame(
                expected: $rateObj->rate,
                actual: $item->vatRate,
                message: "Resolved object for $rateObj->rate does not match amount."
            );
        }
    }
}
