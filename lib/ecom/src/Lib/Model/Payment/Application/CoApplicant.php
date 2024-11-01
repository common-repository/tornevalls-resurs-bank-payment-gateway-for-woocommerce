<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Application;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * CoApplicant for Customer models in MAPI.
 */
class CoApplicant extends Model
{
    /**
     * @throws IllegalValueException
     * @throws EmptyValueException
     */
    public function __construct(
        /**
         * @todo Not sure how to validate government id.
         */
        public readonly string $governmentId,
        /**
         * @todo Not sure how to validate phone number.
         */
        public readonly ?string $mobilePhone = null,
        /**
         * @todo Not sure how to validate phone number.
         */
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateGovernmentId();
        $this->validateEmail();
    }

    /**
     * @throws EmptyValueException
     */
    private function validateGovernmentId(): void
    {
        $this->stringValidation->notEmpty(value: $this->governmentId);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateEmail(): void
    {
        if (!$this->email) {
            return;
        }

        $this->stringValidation->isEmail(value: $this->email);
    }
}
