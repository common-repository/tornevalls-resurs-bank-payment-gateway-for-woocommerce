<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Woocommerce\Modules\OrderManagement\Action;

use Resursbank\Ecom\Module\Payment\Enum\ActionType;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Woocommerce\Database\Options\OrderManagement\EnableCapture;
use Resursbank\Woocommerce\Modules\OrderManagement\Action;
use Resursbank\Woocommerce\Modules\OrderManagement\OrderManagement;
use WC_Order;

/**
 * Business logic to capture Resurs Bank payment.
 */
class Capture extends Action
{
    /**
     * Capture Resurs Bank payment.
     */
    public static function exec(
        WC_Order $order
    ): void {
        if (!EnableCapture::isEnabled()) {
            return;
        }

        OrderManagement::execAction(
            action: ActionType::CAPTURE,
            order: $order,
            callback: static function () use ($order): void {
                $payment = OrderManagement::getPayment(order: $order);

                if (!$payment->canCapture()) {
                    return;
                }

                $transactionId = self::generateTransactionId();

                $response = Repository::capture(
                    paymentId: $payment->id,
                    transactionId: $transactionId
                );

                $action = $response->order?->actionLog->getByTransactionId(
                    id: $transactionId
                );

                OrderManagement::logSuccessPaymentAction(
                    action: ActionType::CAPTURE,
                    order: $order,
                    amount: $action?->orderLines->getTotal()
                );
            }
        );
    }
}
