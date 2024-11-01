<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\UpdatePayment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Response to UpdatePayment calls.
 */
class Response extends Model
{
    public function __construct(
        public string $message,
        public int $code
    ) {
    }
}
