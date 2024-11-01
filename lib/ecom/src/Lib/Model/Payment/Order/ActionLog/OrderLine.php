<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Validation\FloatValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

use function is_float;
use function is_string;

/**
 * Defines a product in an order.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class OrderLine extends Model
{
    /**
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly float $quantity,
        public readonly string $quantityUnit,
        public readonly float $vatRate,
        public readonly float $totalAmountIncludingVat,
        public readonly ?string $description = null,
        public readonly ?string $reference = null,
        public readonly ?OrderLineType $type = null,
        public readonly ?float $unitAmountIncludingVat = null,
        public readonly ?float $totalVatAmount = null,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly FloatValidation $floatValidation = new FloatValidation()
    ) {
        $this->validateDescription();
        $this->validateReference();
        $this->validateQuantityUnit();
        $this->validateVatRate();
        $this->validateQuantity();
        $this->validateUnitAmountIncludingVat();
        $this->validateTotalAmountIncludingVat();
        $this->validateTotalVatAmount();
    }

    /**
     * @throws IllegalValueException
     */
    private function validateDescription(): void
    {
        if (!is_string(value: $this->description)) {
            return;
        }

        $this->stringValidation->length(
            value: $this->description,
            min: 0,
            max: 100
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateReference(): void
    {
        if (!is_string(value: $this->reference)) {
            return;
        }

        $this->stringValidation->length(
            value: $this->reference,
            min: 0,
            max: 50
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateQuantityUnit(): void
    {
        $this->stringValidation->length(
            value: $this->quantityUnit,
            min: 0,
            max: 50
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateVatRate(): void
    {
        $this->floatValidation->length(value: $this->vatRate, min: 0, max: 2);

        $this->floatValidation->inRange(
            value: $this->vatRate,
            min: 0,
            max: 99.99
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateQuantity(): void
    {
        $this->floatValidation->length(value: $this->quantity, min: 0, max: 2);

        $this->floatValidation->inRange(
            value: $this->quantity,
            min: 0,
            max: 9999999999.99
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateUnitAmountIncludingVat(): void
    {
        if (!is_float(value: $this->unitAmountIncludingVat)) {
            return;
        }

        $this->floatValidation->length(
            value: $this->unitAmountIncludingVat,
            min: 0,
            max: 2
        );

        $this->floatValidation->inRange(
            value: $this->unitAmountIncludingVat,
            min: -9999999999.99,
            max: 9999999999.99
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateTotalAmountIncludingVat(): void
    {
        $this->floatValidation->length(
            value: $this->totalAmountIncludingVat,
            min: 0,
            max: 2
        );

        $this->floatValidation->inRange(
            value: $this->totalAmountIncludingVat,
            min: -9999999999.99,
            max: 9999999999.99
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateTotalVatAmount(): void
    {
        if (!is_float(value: $this->totalVatAmount)) {
            return;
        }

        $this->floatValidation->length(
            value: $this->totalVatAmount,
            min: 0,
            max: 2
        );

        $this->floatValidation->inRange(
            value: $this->totalVatAmount,
            min: -9999999999.99,
            max: 9999999999.99
        );
    }
}
