<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\AnnuityFactor\Models;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Describes an annuity factor.
 */
class AnnuityInformation extends Model
{
    public function __construct(
        public readonly string $paymentPlanName,
        public readonly float $annuityFactor,
        public readonly int $durationMonths,
        public readonly float $administrationFee,
        public readonly float $setupFee
    ) {
    }
}
