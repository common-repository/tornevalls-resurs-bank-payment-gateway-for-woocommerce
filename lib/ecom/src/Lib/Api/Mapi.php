<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * API credentials configuration object.
 */
class Mapi
{
    /**
     * Production endpoint.
     */
    public const URL_PROD = 'https://merchant-api.resurs.com/';

    /**
     * Test endpoint.
     */
    public const URL_TEST = 'https://merchant-api.integration.resurs.com/';

    /**
     * Common prefix route name for all API calls.
     */
    public const STORE_ROUTE = 'v2/stores';

    /**
     * Prefix route name for payment based API calls.
     */
    public const PAYMENT_ROUTE = 'v2/payments';

    /**
     * Prefix route name for customer related API calls.
     */
    public const CUSTOMER_ROUTE = 'v2/customers';

    /**
     * Prefix route name for callback related API calls.
     */
    public const CALLBACK_ROUTE = 'v2/callbacks';

    public function __construct(
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
    }

    /**
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws ConfigException
     * @todo Check if ConfigException validation needs a test.
     */
    public function getUrl(
        string $route
    ): string {
        $this->stringValidation->notEmpty(value: $route);

        return
            (Config::isProduction() ? self::URL_PROD : self::URL_TEST) .
            $route
        ;
    }
}
