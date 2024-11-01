<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Payment\Order\ActionLog;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine as OrderLineModel;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\OrderLineType;

/**
 * Test data integrity of OrderLineCollection entity.
 */
class OrderLineCollectionTest extends TestCase
{
    /**
     * Assert getTotal() method results in total of all totalAmountIncludingVat
     * properties on each OrderLine instance in collection.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testGetTotal(): void
    {
        $collection = new OrderLineCollection(data: [
            new OrderLineModel(
                quantity: 2.00,
                quantityUnit: 'st',
                vatRate: 25.00,
                totalAmountIncludingVat: 250,
                description: 'A great white shark',
                reference: 'great-shark',
                type: OrderLineType::PHYSICAL_GOODS,
                unitAmountIncludingVat: 125,
                totalVatAmount: 50
            ),
            new OrderLineModel(
                quantity: 5.00,
                quantityUnit: 'st',
                vatRate: 25.00,
                totalAmountIncludingVat: 512.9,
                description: 'Paper cutter',
                reference: 'paper-cutter',
                type: OrderLineType::PHYSICAL_GOODS,
                unitAmountIncludingVat: 102.58,
                totalVatAmount: 102.58
            ),
        ]);

        $this->assertSame(expected: 762.9, actual: $collection->getTotal());
    }
}
