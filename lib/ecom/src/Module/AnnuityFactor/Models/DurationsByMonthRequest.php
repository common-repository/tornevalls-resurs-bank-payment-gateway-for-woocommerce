<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\AnnuityFactor\Models;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Describes a DurationsByMonth request object
 */
class DurationsByMonthRequest extends Model
{
    /**
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $paymentMethodId,
        private readonly StringValidation $stringValidator = new StringValidation()
    ) {
        $this->validatePaymentMethodId();
    }

    /**
     * @throws IllegalValueException
     */
    private function validatePaymentMethodId(): void
    {
        $this->stringValidator->isUuid(value: $this->paymentMethodId);
    }
}
