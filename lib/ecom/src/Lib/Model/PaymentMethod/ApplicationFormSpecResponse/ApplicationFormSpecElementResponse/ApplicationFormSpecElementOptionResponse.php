<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse\ApplicationFormSpecElementResponse;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Only used on elements of Type LIST. Represents one option in a multiple-choice input element.
 */
class ApplicationFormSpecElementOptionResponse extends Model
{
    /**
     * @throws EmptyValueException
     */
    public function __construct(
        public readonly string $label,
        public readonly string $value,
        public readonly ?bool $checked = null,
        public readonly ?string $description = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateLabel();
        $this->validateValue();
    }

    /**
     * @throws EmptyValueException
     */
    private function validateLabel(): void
    {
        $this->stringValidation->notEmpty(value: $this->label);
    }

    /**
     * @throws EmptyValueException
     */
    private function validateValue(): void
    {
        $this->stringValidation->notEmpty(value: $this->value);
    }
}
