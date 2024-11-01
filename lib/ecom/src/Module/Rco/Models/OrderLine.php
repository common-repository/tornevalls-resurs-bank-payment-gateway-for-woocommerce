<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines an UpdatePayment order line object.
 */
class OrderLine extends Model
{
    public function __construct(
        public string $artNo,
        public ?string $description,
        public float $quantity,
        public string $unitMeasure,
        public float $unitAmountWithoutVat,
        public float $vatPct,
        public ?string $type = null
    ) {
    }
}
