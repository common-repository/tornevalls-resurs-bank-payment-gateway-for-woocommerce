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
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Request;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Response;
use Resursbank\Ecom\Module\Rco\Repository;
use stdClass;

/**
 * Handles updates of RCO payment sessions.
 */
class UpdatePayment
{
    /**
     * Makes call to the API.
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
     * @todo Consider using the LogException trait instead.
     * @todo Class has been discussed for refactoring.
     * @todo Ensure the PHPStan suppressors are removed when we refactor.
     */
    public function call(Request $request, string $orderReference): Response
    {
        try {
            $response = Curl::put(
                url: $this->getApiUrl(orderReference: $orderReference),
                payload: $request->toArray(),
                authType: AuthType::BASIC,
                responseContentType: ContentType::RAW
            );
        } catch (CurlException $exception) {
            Config::getLogger()->error(message: $exception);
            throw $exception;
        }

        $responseObj = new stdClass();

        /* @phpstan-ignore-next-line */
        $responseObj->message = $response->body->message;
        $responseObj->code = $response->code;

        /* @phpstan-ignore-next-line */
        return DataConverter::stdClassToType(
            object: $responseObj,
            type: Response::class
        );
    }

    /**
     * @throws ConfigException
     * @todo Check if ConfigException validation needs a test.
     */
    private function getApiUrl(string $orderReference): string
    {
        return 'https://' . Repository::getApiHostname() . '/checkout/payments/' . $orderReference;
    }
}
