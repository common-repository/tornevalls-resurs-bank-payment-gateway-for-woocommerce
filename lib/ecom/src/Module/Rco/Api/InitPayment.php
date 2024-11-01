<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Api;

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
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Request;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Response;
use Resursbank\Ecom\Module\Rco\Repository;

/**
 * Handles creation of RCO payment sessions.
 */
class InitPayment
{
    /**
     * Makes call to the API
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @throws ConfigException
     * @throws IllegalValueException
     * @todo Check if ConfigException validation needs a test.
     * @todo Consider using LogException trait instead.
     * @todo Class has been discussed for refactoring.
     * @todo Ensure the PHPStan suppressors are removed when we refactor.
     */
    public function call(Request $request, string $orderReference): Response
    {
        try {
            $response = Curl::post(
                url: $this->getApiUrl(orderReference: $orderReference),
                payload: $request->toArray(),
                authType: AuthType::BASIC
            );
        } catch (CurlException $exception) {
            Config::getLogger()->error(message: $exception);
            throw $exception;
        }

        /* @phpstan-ignore-next-line */
        return DataConverter::stdClassToType(
            /* @phpstan-ignore-next-line */
            object: $response->body,
            type: Response::class
        );
    }

    /**
     * Gets the API URL to use
     *
     * @throws ConfigException
     * @todo Check if ConfigException validation needs a test.
     */
    private function getApiUrl(string $orderReference): string
    {
        return 'https://' . Repository::getApiHostname() . '/checkout/payments/' . $orderReference;
    }
}