<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\LegalLink\Type as LegalLinkType;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\AnnuityFactor\Models\AnnuityInformation;
use Resursbank\Ecom\Module\AnnuityFactor\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\PriceSignage\Models\Cost;
use Resursbank\Ecom\Module\PriceSignage\Repository as SignageRepository;

/**
 * Renders Part payment widget HTML and CSS
 */
class PartPayment extends Widget
{
    /** @var string */
    public readonly string $logo;

    /** @var string */
    public readonly string $infoText;

    /** @var string */
    public readonly string $content;

    /** @var string */
    public readonly string $css;

    /** @var string */
    public readonly string $readMore;

    /** @var string */
    public readonly string $iframeUrl;

    /** @var string */
    public readonly string $startingAt;

    /** @var string */
    public readonly string $error;

    /** @var string */
    public readonly string $js;

    /** @var Cost */
    public readonly Cost $cost;

    /** @var AnnuityInformation */
    private readonly AnnuityInformation $annuityInformation;

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ValidationException
     */
    public function __construct(
        private readonly string $storeId,
        private readonly PaymentMethod $paymentMethod,
        private readonly int $months,
        private readonly float $amount,
        public readonly string $currencySymbol,
        public readonly CurrencyFormat $currencyFormat,
        public readonly string $apiUrl,
        public readonly int $decimals = 2
    ) {
        $this->annuityInformation = $this->getAnnuityInformation();
        $this->cost = $this->getCost();
        $this->logo = (string) file_get_contents(
            filename: __DIR__ . '/resurs.svg'
        );
        $this->infoText = Translator::translate(
            phraseId: 'pay-in-installments-with-resurs-bank'
        );
        $this->startingAt = $this->getStartingAt();
        $this->readMore = Translator::translate(phraseId: 'read-more');
        $this->iframeUrl = $this->getIframeUrl();
        $this->error = Translator::translate(
            phraseId: 'part-payment-general-error'
        );

        $this->content = $this->render(file: __DIR__ . '/part-payment.phtml');
        $this->css = $this->render(file: __DIR__ . '/part-payment.css');
        $this->js = $this->render(file: __DIR__ . '/part-payment-js.phtml');
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Return payment method
     */
    public function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    /**
     * Fetches translated and formatted "Starting at %1 per month..." string
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function getStartingAt(): string
    {
        return str_replace(
            search: ['%1', '%2'],
            replace: [
                '<span id="rb-pp-starting-at">' . $this->getFormattedStartingAtCost() . '</span>',
                $this->annuityInformation->paymentPlanName,
            ],
            subject: Translator::translate(phraseId: 'starting-at')
        );
    }

    /**
     * Fetches formatted starting at cost with currency symbol
     */
    public function getFormattedStartingAtCost(): string
    {
        if ($this->currencyFormat === CurrencyFormat::SYMBOL_FIRST) {
            return $this->currencySymbol . ' ' . $this->getStartingAtCost();
        }

        return $this->getStartingAtCost() . ' ' . $this->currencySymbol;
    }

    /**
     * Returns the starting at value, public visibility so that just the value can be extracted for AJAX purposes.
     */
    public function getStartingAtCost(): string
    {
        return number_format(
            num: round(
                num: $this->cost->monthlyCost,
                precision: 2
            ),
            decimals: $this->decimals,
            decimal_separator: ',',
            thousands_separator: ' '
        );
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws MissingKeyException
     * @throws ReflectionException
     * @throws ValidationException
     */
    private function getAnnuityInformation(): AnnuityInformation
    {
        $annuityFactors = Repository::getAnnuityFactors(
            storeId: $this->storeId,
            paymentMethodId: $this->paymentMethod->id
        );

        /** @var AnnuityInformation $annuityFactor */
        foreach ($annuityFactors->content as $annuityFactor) {
            if ($annuityFactor->durationMonths === $this->months) {
                return $annuityFactor;
            }
        }

        throw new MissingKeyException(
            message: 'Could not find matching payment plan'
        );
    }
    /*
     * Return total amount of product
     *
     * @return float
     */

    /**
     * Fetch a Cost object from the Price signage API
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    private function getCost(): Cost
    {
        $costs = SignageRepository::getPriceSignage(
            storeId: $this->storeId,
            paymentMethodId: $this->paymentMethod->id,
            amount: $this->amount,
            monthFilter: $this->months
        );

        if (empty($costs->costList->toArray())) {
            throw new EmptyValueException(
                message: 'Returned CostCollection appears to be empty'
            );
        }

        if (sizeof($costs->costList) > 1) {
            throw new IllegalValueException(
                message: 'Returned CostCollection contains more than one Cost'
            );
        }

        return array_values(array: $costs->costList->toArray())[0];
    }

    /**
     * Fetches iframe URL
     *
     * @todo: Properly render URL
     */
    private function getIframeUrl(): string
    {
        /** @var PaymentMethod\LegalLink $legalLink */
        foreach ($this->paymentMethod->legalLinks as $legalLink) {
            if ($legalLink->type === LegalLinkType::PRICE_INFO) {
                return $legalLink->url . $this->amount;
            }
        }

        return '';
    }
}
