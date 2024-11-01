<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\InitPayment;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Rco\Models\MetaDataCollection;
use Resursbank\Ecom\Module\Rco\Models\OrderLineCollection;

/**
 * Defines an InitPayment request object.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Request extends Model
{
    public function __construct(
        public OrderLineCollection $orderLines,
        public Customer $customer,
        public string $successUrl,
        public string $backUrl,
        public string $shopUrl,
        public ?string $paymentCreatedCallbackUrl = null,
        public ?MetaDataCollection $metaData = null
    ) {
    }
}
