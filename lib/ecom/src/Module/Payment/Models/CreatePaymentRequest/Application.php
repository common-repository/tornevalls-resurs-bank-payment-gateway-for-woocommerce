<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Lib\Validation\FloatValidation;

/**
 * Application data for a payment.
 */
class Application extends Model
{
    /**
     * @param array|null $applicationData
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly ?float $requestedCreditLimit,
        public readonly ?array $applicationData,
        private readonly FloatValidation $floatValidation = new FloatValidation(),
        private readonly ArrayValidation $arrayValidation = new ArrayValidation()
    ) {
        $this->validateRequestedCreditLimit();
        $this->validateApplicationData();
    }

    /**
     * @throws IllegalValueException
     */
    private function validateRequestedCreditLimit(): void
    {
        if ($this->requestedCreditLimit === null) {
            return;
        }

        $this->floatValidation->inRange(
            value: $this->requestedCreditLimit,
            min: 1,
            max: 9999999999
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateApplicationData(): void
    {
        if ($this->applicationData === null) {
            return;
        }

        $this->arrayValidation->isAssoc(data: $this->applicationData);
    }
}
