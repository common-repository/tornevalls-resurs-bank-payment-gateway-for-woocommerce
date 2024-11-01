<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Module\Rco\Api\GetPayment;
use Resursbank\Ecom\Module\Rco\Api\InitPayment;
use Resursbank\Ecom\Module\Rco\Api\UpdatePayment;
use Resursbank\Ecom\Module\Rco\Api\UpdatePaymentReference;
use Resursbank\Ecom\Module\Rco\Models\GetPayment\Response;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Request as InitPaymentRequest;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Response as InitPaymentResponse;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Request as UpdatePaymentRequest;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Response as UpdatePaymentResponse;
use Resursbank\Ecom\Module\Rco\Models\UpdatePaymentReference\Request as UpdatePaymentReferenceRequest;
use Resursbank\Ecom\Module\Rco\Models\UpdatePaymentReference\Response as UpdatePaymentReferenceResponse;

/**
 * Main entrypoint for interfacing with the RCO API programmatically.
 */
class Repository
{
    public const HOSTNAME_PROD = 'checkout.resurs.com';
    public const HOSTNAME_TEST = 'omnitest.resurs.com';

    /**
     * Initialize a payment session.
     *
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @throws IllegalValueException
     */
    public static function initPayment(InitPaymentRequest $request, string $orderReference): InitPaymentResponse
    {
        return (new InitPayment())
            ->call(request: $request, orderReference: $orderReference);
    }

    /**
     * Update an existing payment session.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function updatePayment(UpdatePaymentRequest $request, string $orderReference): UpdatePaymentResponse
    {
        return (new UpdatePayment())
            ->call(request: $request, orderReference: $orderReference);
    }

    /**
     * Update the payment reference for a payment session.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function updatePaymentReference(
        UpdatePaymentReferenceRequest $request,
        string $orderReference
    ): UpdatePaymentReferenceResponse {
        return (new UpdatePaymentReference())
            ->call(request: $request, orderReference: $orderReference);
    }

    /**
     * Get existing payment session.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @noinspection PhpUnused
     */
    public static function getPayment(string $orderReference): Response
    {
        return (new GetPayment())
            ->call(orderReference: $orderReference);
    }

    /**
     * Gets API hostname.
     *
     * @throws ConfigException
     * @todo Check if ConfigException validation needs a test.
     */
    public static function getApiHostname(): string
    {
        if (Config::isProduction()) {
            return self::HOSTNAME_PROD;
        }

        return self::HOSTNAME_TEST;
    }
}
