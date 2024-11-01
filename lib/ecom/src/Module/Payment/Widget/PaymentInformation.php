<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;

/**
 * Renders Payment Information widget for use in admin panel order view
 *
 * @todo Refactor this file. Contains several null pointers, file_get_contents can return false, etc.
 */
class PaymentInformation extends Widget
{
    /** @var Payment */
    public readonly Payment $payment;

    /** @var string */
    public readonly string $content;

    /** @var string */
    public readonly string $css;

    /** @var string */
    public readonly string $logo;

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws FilesystemException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly string $currencySymbol,
        public readonly CurrencyFormat $currencyFormat
    ) {
        $this->payment = Repository::get(paymentId: $this->paymentId);

        $this->logo = file_get_contents(filename: __DIR__ . '/resurs.svg');
        $this->content = $this->render(
            file: __DIR__ . '/payment-information.phtml'
        );
        $this->css = $this->render(file: __DIR__ . '/payment-information.css');
    }

    /**
     * Fetches CSS without instantiating an object.
     */
    public static function getCss(): string
    {
        return file_get_contents(
            filename: __DIR__ . '/payment-information.css'
        );
    }

    /**
     * Fetch payment status.
     */
    public function getStatus(): string
    {
        return $this->payment->status->name;
    }

    /**
     * Fetch the name of the payment method used.
     */
    public function getPaymentMethodName(): string
    {
        return $this->payment->paymentMethod->name;
    }

    /**
     * Fetch customer name.
     */
    public function getCustomerName(): string
    {
        return $this->payment->customer->deliveryAddress->fullName;
    }

    /**
     * Fetch formatted delivery address.
     */
    public function getAddress(): string
    {
        return $this->payment->customer->deliveryAddress->addressRow1 . '<br />' . PHP_EOL .
            ($this->payment->customer->deliveryAddress->addressRow2 ?
                $this->payment->customer->deliveryAddress->addressRow2 . '<br />' . PHP_EOL :
                ''
            ) .
            $this->payment->customer->deliveryAddress->postalArea . '<br />' . PHP_EOL .
            $this->payment->customer->deliveryAddress->countryCode->value . ' - ' .
            $this->payment->customer->deliveryAddress->postalCode;
    }

    /**
     * Fetch customer mobile phone number from payment.
     */
    public function getTelephone(): string
    {
        return $this->payment->customer->mobilePhone;
    }

    /**
     * Fetch customer email from payment.
     */
    public function getEmail(): string
    {
        return $this->payment->customer->email;
    }

    public function getFormattedAmount(float $amount): string
    {
        if ($this->currencyFormat === CurrencyFormat::SYMBOL_FIRST) {
            return $this->currencySymbol . ' ' . $amount;
        }

        return $amount . ' ' . $this->currencySymbol;
    }
}
