<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\AnnuityFactor;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Module\AnnuityFactor\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;

/**
 * Integration tests for AnnuityFactors repository.
 */
class RepositoryTest extends TestCase
{
    private Cache $cache;

    private string $storeId;

    private string $paymentMethodId;

    /**
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    protected function setUp(): void
    {
        $this->storeId = $_ENV['STORE_ID'];
        $this->paymentMethodId = $_ENV['ANNUITY_PAYMENT_METHOD_ID'];

        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(
                path: '/tmp/ecom-test/annuityFactors/' . time()
            ),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );

        $this->cache = Repository::getCache(
            storeId: $this->storeId,
            paymentMethodId: $this->paymentMethodId
        );

        $this->cache->clear();

        parent::setUp();
    }

    /**
     * Assert clearCache() clears cache.
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
    public function testClearCache(): void
    {
        Repository::getAnnuityFactors(
            storeId: $this->storeId,
            paymentMethodId: $this->paymentMethodId
        );

        $this->assertNotNull(actual: $this->cache->read());

        $this->cache->clear();

        $this->assertNull(actual: $this->cache->read());
    }

    /**
     * Assert read() returns data from the API when cache is empty.
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
    public function testGetAnnuityFactorsReturnsWithoutCache(): void
    {
        $this->assertNull(actual: $this->cache->read());
        $this->assertNotEmpty(
            actual: Repository::getAnnuityFactors(
                storeId: $this->storeId,
                paymentMethodId: $this->paymentMethodId
            )
        );
    }

    /**
     * Assert read() retrieves payment methods, paymentMethod them in cache, and
     * will later return the same paymentMethods from cache.
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
    public function testGetAnnuityFactorsReturnsCache(): void
    {
        $this->assertEmpty(actual: $this->cache->read());

        $data = Repository::getAnnuityFactors(
            storeId: $this->storeId,
            paymentMethodId: $this->paymentMethodId
        );

        $this->assertNotEmpty(actual: $data);

        /* Since we cannot mock the API adapter we will need to call the
            readCache() directly to ensure we don't fetch from the API again. */
        $this->assertEquals(expected: $data, actual: $this->cache->read());
    }

    /**
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
     * @todo Compares lengths of original methods collection and the filtered
     *      one. The filtered should have a shorter length.
     */
    public function testGetMethodsReturnsFilteredCollection(): void
    {
        $filteredMethods = Repository::getMethods(
            storeId: $this->storeId,
            paymentMethods: PaymentMethodRepository::getPaymentMethods(
                storeId: $this->storeId
            )
        );

        $this->assertNotEmpty(actual: $filteredMethods->toArray());
    }
}
