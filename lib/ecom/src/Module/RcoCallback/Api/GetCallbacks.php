<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\RcoCallback\Api;

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
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\RcoCallback\Models\Callback;
use Resursbank\Ecom\Module\RcoCallback\Models\CallbackCollection;
use Resursbank\Ecom\Module\RcoCallback\Repository;

/**
 * Handles fetching of a list of configured callbacks
 */
class GetCallbacks
{
    /**
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
     * @todo I dropped an EmptyValueException, ensure tests are fine.
     * @todo Class has been discussed for refactoring.
     * @todo Ensure the PHPStan suppressors are removed when we refactor.
     */
    public function call(): CallbackCollection
    {
        $curl = new Curl(
            url: $this->getApiUrl(),
            requestMethod: RequestMethod::GET,
            contentType: ContentType::EMPTY,
            authType: AuthType::BASIC,
            responseContentType: ContentType::JSON
        );

        try {
            $response = $curl->exec();

            /* @phpstan-ignore-next-line */
            return DataConverter::arrayToCollection(
                /* @phpstan-ignore-next-line */
                data: $response->body,
                type: Callback::class
            );
        } catch (CurlException $exception) {
            Config::getLogger()->error(message: $exception);
            throw $exception;
        }
    }

    /**
     * Gets the API URL to use
     *
     * @throws ConfigException
     */
    private function getApiUrl(): string
    {
        return 'https://' . Repository::getApiHostname() . '/callbacks';
    }
}
