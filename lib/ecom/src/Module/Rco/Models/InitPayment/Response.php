<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\InitPayment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines an InitPayment response object.
 */
class Response extends Model
{
    public function __construct(
        public readonly ?string $paymentSessionId = null,
        public readonly ?string $iframe = null,
        public readonly ?string $script = null,
        public readonly ?Customer $customer = null,
        public readonly ?string $baseUrl = null,
        public readonly ?string $html = null
    ) {
    }
}
