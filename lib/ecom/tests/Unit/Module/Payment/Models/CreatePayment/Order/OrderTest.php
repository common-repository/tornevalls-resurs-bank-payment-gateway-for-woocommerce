<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Payment\Models\CreatePayment\Order;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Order;

/**
 * Test data integrity of order entity model.
 */
class OrderTest extends TestCase
{
    private static OrderLine $orderLine;

    /**
     * @throws IllegalValueException
     */
    protected function setUp(): void
    {
        self::$orderLine = new OrderLine(
            description: 'Item',
            quantity: 1,
            reference: 'I-200',
            type: OrderLineType::NORMAL,
            quantityUnit: 'st',
            unitAmountIncludingVat: 10,
            totalAmountIncludingVat: 11,
            totalVatAmount: 1,
            vatRate: 10
        );

        parent::setUp();
    }

    /**
     * Assert validateDescription() throws IllegalValueException when its
     * length is too long.
     *
     * @throws IllegalTypeException
     * @throws IllegalCharsetException
     */
    public function testValidateOrderLinesThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Order(
            orderLines: new OrderLineCollection(
                data: array_fill(
                    start_index: 0,
                    count: 1001,
                    value: self::$orderLine
                )
            )
        );
    }

    /**
     * Assert validateOrderReference() throws IllegalValueException when it's
     * empty.
     *
     * @throws IllegalTypeException
     * @throws IllegalCharsetException
     */
    public function testValidateOrderReferenceThrowsWhenEmpty(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Order(
            orderLines: new OrderLineCollection(
                data: array_fill(
                    start_index: 0,
                    count: 5,
                    value: self::$orderLine
                )
            ),
            orderReference: ''
        );
    }

    /**
     * Assert validateOrderReference() throws IllegalValueException when it's
     * too long.
     *
     * @throws IllegalTypeException
     * @throws IllegalCharsetException
     */
    public function testValidateOrderReferenceThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Order(
            orderLines: new OrderLineCollection(
                data: array_fill(
                    start_index: 0,
                    count: 5,
                    value: self::$orderLine
                )
            ),
            orderReference: 'asdf asdf asdf asdf asdf asdf asd'
        );
    }

    /**
     * Assert validateOrderReference() throws IllegalValueException when it's
     * using illegal characters.
     *
     * @throws IllegalTypeException
     * @throws IllegalCharsetException
     * @throws IllegalValueException
     */
    public function testValidateOrderReferenceThrowsUsingIllegalCharacters(): void
    {
        new OrderLineCollection(
            data: array_fill(
                start_index: 0,
                count: 5,
                value: self::$orderLine
            )
        );

        $this->expectException(exception: IllegalCharsetException::class);
        new Order(
            orderLines: new OrderLineCollection(
                data: array_fill(
                    start_index: 0,
                    count: 5,
                    value: self::$orderLine
                )
            ),
            orderReference: 'äåö'
        );
    }
}
