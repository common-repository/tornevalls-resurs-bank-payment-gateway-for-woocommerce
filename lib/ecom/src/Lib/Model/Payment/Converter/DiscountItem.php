<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Converter;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\FloatValidation;

/**
 * Object containing amount of discount applied with specific VAT rate.
 */
class DiscountItem extends Model
{
    /**
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly float $rate,
        public float $amount = 0.0,
        private readonly FloatValidation $floatValidation = new FloatValidation()
    ) {
        $this->validateRate();
        $this->validateAmount();
    }

    /**
     * @throws IllegalValueException
     */
    private function validateRate(): void
    {
        $this->floatValidation->length(value: $this->rate, min: 0, max: 2);
        $this->floatValidation->inRange(value: $this->rate, min: 0, max: 99.99);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateAmount(): void
    {
        $this->floatValidation->length(value: $this->amount, min: 0, max: 2);

        $this->floatValidation->inRange(
            value: $this->amount,
            min: 0.0,
            max: 9999999999.99
        );
    }
}
