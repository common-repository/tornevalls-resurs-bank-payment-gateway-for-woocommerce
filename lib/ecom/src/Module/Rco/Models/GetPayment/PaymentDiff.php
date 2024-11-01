<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\GetPayment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines a history entry (change tracking) to a payment.
 */
class PaymentDiff extends Model
{
    /**
     * @param array $documentNames
     */
    public function __construct(
        public string $type,
        public string $created,
        public PaymentSpec $paymentSpec,
        public array $documentNames,
        public ?string $createdBy = null,
        public ?string $orderId = null,
        public ?string $invoiceId = null,
        public ?string $transactionId = null
    ) {
    }
}
