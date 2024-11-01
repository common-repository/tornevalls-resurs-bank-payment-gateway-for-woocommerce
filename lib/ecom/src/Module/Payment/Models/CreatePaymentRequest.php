<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Metadata;
use Resursbank\Ecom\Lib\Model\Payment\Order;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Application;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options;

/**
 * Payment model used in a POST /payments request.
 */
class CreatePaymentRequest extends Model
{
    /**
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $storeId,
        public readonly string $paymentMethodId,
        public readonly Order $order,
        public readonly ?Application $application,
        public readonly ?Customer $customer,
        public readonly ?Metadata $metadata,
        public readonly ?Options $options,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateStoreId();
        $this->validatePaymentMethodId();
    }

    /**
     * @throws IllegalValueException
     */
    private function validateStoreId(): void
    {
        $this->stringValidation->isUuid(value: $this->storeId);
    }

    /**
     * @throws IllegalValueException
     */
    private function validatePaymentMethodId(): void
    {
        $this->stringValidation->isUuid(value: $this->paymentMethodId);
    }
}
