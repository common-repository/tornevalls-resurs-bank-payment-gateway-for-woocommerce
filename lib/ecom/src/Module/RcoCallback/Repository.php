<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\RcoCallback;

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
use Resursbank\Ecom\Module\RcoCallback\Api\DeleteCallback;
use Resursbank\Ecom\Module\RcoCallback\Api\GetCallback;
use Resursbank\Ecom\Module\RcoCallback\Api\GetCallbacks;
use Resursbank\Ecom\Module\RcoCallback\Api\RegisterCallback;
use Resursbank\Ecom\Module\RcoCallback\Models\Callback;
use Resursbank\Ecom\Module\RcoCallback\Models\CallbackCollection;
use Resursbank\Ecom\Module\RcoCallback\Models\RegisterCallback\Request;

/**
 * Main entrypoint for interfacing with the RCO callback API programmatically
 */
class Repository
{
    public const HOSTNAME_PROD = 'checkout.resurs.com';
    public const HOSTNAME_TEST = 'omnitest.resurs.com';

    /**
     * Registers a new callback
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
    public static function registerCallback(string $eventName, Request $request): void
    {
        (new RegisterCallback())
            ->call(eventName: $eventName, request: $request);
    }

    /**
     * Gets a named callback
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
    public static function getCallback(string $eventName): Callback
    {
        return (new GetCallback())
            ->call(eventName: $eventName);
    }

    /**
     * Gets all registered callbacks
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
    public static function getCallbacks(): CallbackCollection
    {
        return (new GetCallbacks())
            ->call();
    }

    /**
     * Deletes a callback
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
    public static function deleteCallback(string $eventName): int
    {
        return (new DeleteCallback())
            ->call(eventName: $eventName);
    }

    /**
     * Gets API hostname
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
