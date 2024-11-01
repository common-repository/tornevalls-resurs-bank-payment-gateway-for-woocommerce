<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Payment;

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
use Resursbank\Ecom\Module\Payment\Enum\PossibleAction;
use Resursbank\Ecom\Module\Payment\Enum\Status;

/**
 * Tests for the Order class.
 */
class OrderTest extends TestCase
{
    /**
     * Create a dummy Payment object with the specified possible actions
     *
     * @throws Exception
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function createDummyPayment(Payment\Order\PossibleActionCollection $possibleActions): Payment
    {
        return new Payment(
            id: Strings::getUuid(),
            created: (new DateTime())->format(format: 'c'),
            storeId: Strings::getUuid(),
            customer: new Payment\Customer(
                customerType: CustomerType::NATURAL
            ),
            paymentMethod: new Payment\PaymentMethod(name: 'Payment method'),
            status: Status::ACCEPTED,
            paymentActions: [],
            order: new Payment\Order(
                orderReference: Strings::getUuid(),
                actionLog: new Payment\Order\ActionLogCollection(data: []),
                possibleActions: $possibleActions,
                totalOrderAmount: 100.00,
                canceledAmount: 0.00,
                authorizedAmount: 100.00,
                capturedAmount: 0.00,
                refundedAmount: 0.00
            )
        );
    }

    /**
     * Verify that the canCancel method works as intended
     *
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testCanCancel(): void
    {
        $cancelable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::CANCEL
                    ),
                ]
            )
        );
        $unCancelable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::REFUND
                    ),
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::PARTIAL_REFUND
                    ),
                ]
            )
        );

        $this->assertEquals(
            expected: true,
            actual: $cancelable->canCancel()
        );
        $this->assertEquals(
            expected: false,
            actual: $unCancelable->canCancel()
        );
    }

    /**
     * Verify that the canCapture method works as intended
     *
     * @throws IllegalTypeException
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    public function testCanCapture(): void
    {
        $captureable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::CAPTURE
                    ),
                ]
            )
        );
        $uncaptureable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::REFUND
                    ),
                ]
            )
        );

        $this->assertEquals(
            expected: true,
            actual: $captureable->canCapture()
        );
        $this->assertEquals(
            expected: false,
            actual: $uncaptureable->canCapture()
        );
    }

    /**
     * Verify that the canCapture method works as intended
     *
     * @throws IllegalTypeException
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    public function testCanPartCapture(): void
    {
        $captureable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::PARTIAL_CAPTURE
                    ),
                ]
            )
        );
        $uncaptureable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::REFUND
                    ),
                ]
            )
        );

        $this->assertEquals(
            expected: true,
            actual: $captureable->canPartiallyCapture()
        );
        $this->assertEquals(
            expected: false,
            actual: $uncaptureable->canPartiallyCapture()
        );
    }

    /**
     * Verify that the canCapture method works as intended
     *
     * @throws IllegalTypeException
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    public function testCanPartCancel(): void
    {
        $captureable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::PARTIAL_CANCEL
                    ),
                ]
            )
        );
        $uncaptureable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::REFUND
                    ),
                ]
            )
        );

        $this->assertEquals(
            expected: true,
            actual: $captureable->canPartiallyCancel()
        );
        $this->assertEquals(
            expected: false,
            actual: $uncaptureable->canPartiallyCancel()
        );
    }

    /**
     * Verify that the canRefund method works as intended
     *
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testCanRefund(): void
    {
        $refundable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::REFUND
                    ),
                ]
            )
        );
        $nonRefundable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::CANCEL
                    ),
                    new Payment\Order\PossibleAction(
                        action: PossibleAction::CAPTURE
                    ),
                ]
            )
        );

        $this->assertEquals(
            expected: true,
            actual: $refundable->canRefund()
        );
        $this->assertEquals(
            expected: false,
            actual: $nonRefundable->canRefund()
        );
    }
}
