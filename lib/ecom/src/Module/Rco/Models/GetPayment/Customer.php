<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\GetPayment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines customer data associated with payment session.
 */
class Customer extends Model
{
    public function __construct(
        public string $governmentId,
        public Address $address,
        public string $phone,
        public string $email,
        public string $type
    ) {
    }
}
