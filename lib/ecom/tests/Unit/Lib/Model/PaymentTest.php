<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Enum\Status;

/**
 * Tests for the Resursbank\Ecom\Lib\Model\Payment class.
 *
 * @todo Missing unit tests ECP-254
 */
class PaymentTest extends TestCase
{
    /**
     * Create a dummy Payment object with the specified status
     *
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    private function createDummyPayment(Status $status): Payment
    {
        return new Payment(
            id: Strings::getUuid(),
            created: (new DateTime())->format(format: 'c'),
            storeId: Strings::getUuid(),
            paymentMethod: new Payment\PaymentMethod(name: 'Payment method'),
            customer: new Payment\Customer(
                customerType: CustomerType::NATURAL
            ),
            status: $status,
            paymentActions: [],
            order: new Payment\Order(
                orderReference: Strings::getUuid(),
                actionLog: new Payment\Order\ActionLogCollection(data: []),
                possibleActions: new Payment\Order\PossibleActionCollection(
                    data: []
                ),
                totalOrderAmount: 100.00,
                canceledAmount: 0.00,
                authorizedAmount: 100.00,
                capturedAmount: 0.00,
                refundedAmount: 0.00
            )
        );
    }

    /**
     * Verify that the isFrozen method works as intended
     *
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsFrozen(): void
    {
        $isFrozen = $this->createDummyPayment(status: Status::FROZEN);
        $notFrozen = $this->createDummyPayment(status: Status::ACCEPTED);

        $this->assertEquals(
            expected: true,
            actual: $isFrozen->isFrozen()
        );
        $this->assertEquals(
            expected: false,
            actual: $notFrozen->isFrozen()
        );
    }
}
