<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines a payment method object returned when fetching a payment
 */
class PaymentMethod extends Model
{
    /**
     * @throws EmptyValueException
     */
    public function __construct(
        public readonly string $name,
        private readonly StringValidation $stringValidator = new StringValidation()
    ) {
        $this->validateName();
    }

    /**
     * @throws EmptyValueException
     */
    private function validateName(): void
    {
        $this->stringValidator->notEmpty(value: $this->name);
    }
}
