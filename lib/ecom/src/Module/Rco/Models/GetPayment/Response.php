<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\GetPayment;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Rco\Models\MetaDataCollection;

/**
 * Defines a response from the GetPayment API call.
 */
class Response extends Model
{
    /**
     * @param array $status
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public string $id,
        public float $totalAmount,
        public float $limit,
        public Customer $customer,
        public Address $deliveryAddress,
        public string $booked,
        public string $paymentMethodId,
        public string $paymentMethodName,
        public bool $fraud,
        public bool $frozen,
        public array $status,
        public string $storeId,
        public string $paymentMethodType,
        public int $totalBonusPoints,
        public ?string $finalized = null,
        public ?MetaDataCollection $metadata = null,
        public ?PaymentDiffCollection $paymentDiffs = null
    ) {
    }
}
