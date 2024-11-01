<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Payment\Order;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Enum\ActionType;

/**
 * Tests for the ActionLog class.
 */
class ActionLogTest extends TestCase
{
    /**
     * Test creating a new instance of ActionLog with supplied parameters.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    private function createActionLog(
        string $created = '2022-11-12 22:12',
        ?string $actionId = null,
        ?OrderLineCollection $orderLines = null
    ): ActionLog {
        // Get default action id.
        if ($actionId === null) {
            $actionId = Strings::getUuid();
        }

        // Get default OrderLineCollection
        if ($orderLines === null) {
            $orderLines = $this->getOrderLines();
        }

        return new ActionLog(
            type: ActionType::CAPTURE,
            actionId: $actionId,
            created: $created,
            orderLines: $orderLines
        );
    }

    /**
     * Resolve mocked OrderLineCollection.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function getOrderLines(): OrderLineCollection
    {
        return new OrderLineCollection(data: [
            new OrderLine(
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
            new OrderLine(
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
    }

    /**
     * Test validation of property "actionId".
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testActionIdProperty(): void
    {
        try {
            $this->createActionLog(actionId: '');
            $this->fail(
                message: 'Could create ActionLog entry with empty actionId value.'
            );
        } catch (EmptyValueException) {
            // Assert EmptyValueException without breaking test method.
            $this->addToAssertionCount(count :1);
        }

        try {
            $this->createActionLog(actionId: 'nothing-to.-say');
            $this->fail(
                message: 'Could create ActionLog entry with illegal actionId value.'
            );
        } catch (IllegalValueException) {
            // Assert IllegalValueException without breaking test method.
            $this->addToAssertionCount(count :1);
        }

        // Assert no exception is thrown.
        $this->createActionLog();
        $this->addToAssertionCount(count :1);
    }

    /**
     * Test validation of property "created".
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testCreatedProperty(): void
    {
        try {
            $this->createActionLog(created: '');
            $this->fail(
                message: 'Could create ActionLog entry with empty created value.'
            );
        } catch (EmptyValueException) {
            // Assert EmptyValueException without breaking test method.
            $this->addToAssertionCount(count :1);
        }

        try {
            $this->createActionLog(actionId: 'This is not a date');
            $this->fail(
                message: 'Could create ActionLog entry with illegal created value.'
            );
        } catch (IllegalValueException) {
            // Assert IllegalValueException without breaking test method.
            $this->addToAssertionCount(count :1);
        }

        // Assert no exception is thrown.
        $this->createActionLog();
        $this->addToAssertionCount(count :1);
    }

    /**
     * Test validation of property "orderLines".
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testOrderLinesProperty(): void
    {
        try {
            $this->createActionLog(
                orderLines: new OrderLineCollection(data: [])
            );
            $this->fail(
                message: 'Could create ActionLog entry with empty orderLines value.'
            );
        } catch (IllegalValueException) {
            // Assert IllegalValueException without breaking test method.
            $this->addToAssertionCount(count :1);
        }

        // Assert no exception is thrown.
        $this->createActionLog();
        $this->addToAssertionCount(count :1);
    }
}
