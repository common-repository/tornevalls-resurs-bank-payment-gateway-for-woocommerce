<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod;

use Exception;
use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Get;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\PaymentMethod\Api\ApplicationDataSpecification;
use Resursbank\Ecom\Module\PaymentMethod\Widget\UniqueSellingPoint;
use Throwable;

/**
 * Interaction with Payment Method entities and related functionality.
 */
class Repository
{
    use ExceptionLog;

    /**
     * NOTE: Parameters must be validated since they are utilized for our cache
     * keys.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    public static function getPaymentMethods(
        string $storeId,
        ?float $amount = null
    ): PaymentMethodCollection {
        try {
            $cache = self::getCache(storeId: $storeId, amount: $amount);
            $result = $cache->read();

            if (!$result instanceof PaymentMethodCollection) {
                $result = self::getApi(
                    storeId: $storeId,
                    amount: $amount
                )->call();

                if (!$result instanceof PaymentMethodCollection) {
                    throw new ApiException(message: 'Invalid API response.');
                }

                $result = self::setCollectionSortOrder(collection: $result);
                $cache->write(data: $result);
            }
        } catch (Throwable $e) {
            self::logException(exception: $e);

            throw $e;
        }

        return $result;
    }

    /**
     * Updates sort order of fetched payment methods.
     */
    public static function setCollectionSortOrder(
        PaymentMethodCollection $collection
    ): PaymentMethodCollection {
        /** @var PaymentMethod $method */
        foreach ($collection as $method) {
            /* @phpstan-ignore-next-line */
            $method->sortOrder = ((int) $collection->key() + 1) * 100;
        }

        return $collection;
    }

    /**
     * @throws IllegalValueException
     */
    public static function getCache(
        string $storeId,
        ?float $amount = null
    ): Cache {
        self::validateStoreId(storeId: $storeId);

        return new Cache(
            key: 'payment-methods-' . sha1(
                string: serialize(value: compact('storeId', 'amount'))
            ),
            model: PaymentMethod::class,
            ttl: 3600
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public static function getApi(
        string $storeId,
        ?float $amount = null
    ): Get {
        self::validateStoreId(storeId: $storeId);

        return new Get(
            model: PaymentMethod::class,
            route: Mapi::STORE_ROUTE . '/' . $storeId . '/payment_methods',
            params: compact('storeId', 'amount'),
            extractProperty: 'content'
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
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function getById(
        string $storeId,
        string $paymentMethodId,
        ?float $amount = null
    ): ?PaymentMethod {
        $result = null;

        $paymentMethods = self::getPaymentMethods(
            storeId: $storeId,
            amount: $amount
        );

        /** @var PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod->id !== $paymentMethodId) {
                continue;
            }

            $result = $paymentMethod;
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public static function getApplicationDataSpecification(
        string $storeId,
        string $paymentMethodId,
        int $amount
    ): PaymentMethod\ApplicationFormSpecResponse {
        try {
            return (new ApplicationDataSpecification())->call(
                storeId: $storeId,
                paymentMethodId: $paymentMethodId,
                amount: $amount
            );
        } catch (Throwable $e) {
            self::logException(exception: $e);
            throw $e;
        }
    }

    /**
     * Fetches the USP for specified payment method type
     */
    public static function getUniqueSellingPoint(
        PaymentMethod $paymentMethod,
        float $amount
    ): UniqueSellingPoint {
        return new UniqueSellingPoint(
            paymentMethod: $paymentMethod,
            amount: $amount
        );
    }

    /**
     * @throws IllegalValueException
     */
    private static function validateStoreId(
        string $storeId
    ): void {
        $stringValidation = new StringValidation();
        $stringValidation->notEmpty(value: $storeId);
        $stringValidation->isUuid(value: $storeId);
    }
}
