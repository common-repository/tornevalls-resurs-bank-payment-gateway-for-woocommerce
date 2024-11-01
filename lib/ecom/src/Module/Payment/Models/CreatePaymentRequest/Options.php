<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\IntValidation;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options\Callbacks;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options\RedirectionUrls;

/**
 * Application data for a payment.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Options extends Model
{
    /**
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly ?bool $initiatedOnCustomersDevice = null,
        public readonly ?bool $handleManualInspection = null,
        public readonly ?bool $handleFrozenPayments = null,
        public readonly bool $automaticCapture = false,
        public readonly ?RedirectionUrls $redirectionUrls = null,
        public readonly ?Callbacks $callbacks = null,
        public readonly ?int $timeToLiveInMinutes = null,
        private readonly IntValidation $intValidation = new IntValidation()
    ) {
        $this->validateTimeToLiveInMinutes();
        $this->validateAutomaticCapture();
    }

    /**
     * @throws IllegalValueException
     */
    private function validateTimeToLiveInMinutes(): void
    {
        if ($this->timeToLiveInMinutes === null) {
            return;
        }

        $this->intValidation->inRange(
            value: $this->timeToLiveInMinutes,
            min: 1,
            max: 43200
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateAutomaticCapture(): void
    {
        if ($this->handleFrozenPayments && $this->automaticCapture) {
            throw new IllegalValueException(
                message: 'automaticCapture cannot be set to true when handleFrozenPayments is set to true'
            );
        }
    }
}
