<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethod;

use Exception;
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
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse\ApplicationFormSpecElementResponse;
use Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse\ApplicationFormSpecElementResponse\Type;
use Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse\ApplicationFormSpecElementResponseCollection;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Module\PaymentMethod\Repository;

/**
 * Integration tests for PaymentMethods repository.
 */
class RepositoryTest extends TestCase
{
    private Cache $cache;

    private string $storeId;

    /**
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    protected function setUp(): void
    {
        $this->storeId = $_ENV['STORE_ID'];

        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(
                path: '/tmp/ecom-test/paymentMethods/' . time()
            ),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );

        $this->cache = Repository::getCache(storeId: $this->storeId);
        $this->cache->clear();

        parent::setUp();
    }

    /**
     * @noinspection PhpSameParameterValueInspection
     */
    private function allFieldsOfType(
        ApplicationFormSpecElementResponseCollection $fields,
        Type $type
    ): bool {
        /** @var ApplicationFormSpecElementResponse $field */
        foreach ($fields as $field) {
            if ($field->type !== $type) {
                return false;
            }
        }

        return true;
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
        Repository::getPaymentMethods(storeId: $this->storeId);

        $this->assertNotNull(actual: $this->cache->read());

        $this->cache->clear();

        $this->assertNull(actual: $this->cache->read());
    }

    /**
     * Assert getPaymentMethods() returns data from the API when cache is empty.
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
    public function testGetPaymentMethodsReturnsWithoutCache(): void
    {
        $this->assertNull(actual: $this->cache->read());
        $this->assertNotEmpty(
            actual: Repository::getPaymentMethods(
                storeId: $this->storeId
            )
        );
    }

    /**
     * Assert getPaymentMethods() retrieves payment methods, paymentMethod them
     * in cache, and will later return the same paymentMethods from cache.
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
    public function testGetPaymentMethodsReturnsCache(): void
    {
        $this->assertEmpty(actual: $this->cache->read());

        $data = Repository::getPaymentMethods(storeId: $this->storeId);

        $this->assertNotEmpty(actual: $data);

        $data->rewind();

        /* Since we cannot mock the API adapter we will need to call the
            readCache() directly to ensure we don't fetch from the API again. */
        $this->assertEquals(
            expected: $data,
            actual: $this->cache->read()
        );
    }

    /**
     * Assert different datasets from the API for different amount values. Also
     * make sure the cache is kept separated by the same value.
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
    public function testDataSeparatedByAmount(): void
    {
        $storeId = $this->storeId;

        $amount1 = 11;
        $amount2 = 1000;

        // Load data from API to cache.
        $apiData1 = Repository::getPaymentMethods(
            storeId: $storeId,
            amount: $amount1
        )->toArray();

        $apiData2 = Repository::getPaymentMethods(
            storeId: $storeId,
            amount: $amount2
        )->toArray();

        // Retrieve same data from cache.
        $cacheData1 = Repository::getCache(
            storeId: $storeId,
            amount: $amount1
        )->read();

        self::assertNotNull(actual: $cacheData1);

        $cacheData1 = $cacheData1->toArray();

        $cacheData2 = Repository::getCache(
            storeId: $storeId,
            amount: $amount2
        )->read();

        self::assertNotNull(actual: $cacheData2);

        $cacheData2 = $cacheData2->toArray();

        $this->assertEquals(expected: $apiData1, actual: $cacheData1);
        $this->assertEquals(expected: $apiData2, actual: $cacheData2);
        $this->assertNotEquals(expected: $apiData1, actual: $apiData2);
        $this->assertNotEquals(expected: $cacheData1, actual: $cacheData2);
    }

    /**
     * Assert getById() returns a payment method by its ID.
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
    public function testGetByIdFindResult(): void
    {
        $paymentMethods = Repository::getPaymentMethods(
            storeId: $this->storeId
        )->toArray();

        /** @var PaymentMethod|null $method */
        $method = $paymentMethods[0] ?? null;

        $this->assertNotNull(actual: $method);

        $paymentMethod = Repository::getById(
            storeId: $this->storeId,
            paymentMethodId: $method->id
        );

        $this->assertNotNull(actual: $paymentMethod);
        $this->assertEquals(expected: $method->id, actual: $paymentMethod->id);
    }

    /**
     * Assert getById() returns NULL when no payment method is found.
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
    public function testGetByIdReturnsNull(): void
    {
        $paymentMethods = Repository::getPaymentMethods(
            storeId: $this->storeId
        )->toArray();

        if (!isset($paymentMethods[0])) {
            $this->fail(message: 'No payment methods found');
        }

        $paymentMethod = Repository::getById(
            storeId: $this->storeId,
            paymentMethodId: 'Not-a-Method'
        );

        $this->assertNull(actual: $paymentMethod);
    }

    /**
     * Performs simple test of application_data_specification fetching
     *
     * @throws Exception
     */
    public function testGetApplicationDataSpecification(): void
    {
        $response = Repository::getApplicationDataSpecification(
            storeId: $this->storeId,
            paymentMethodId: $_ENV['APPLICATION_DATA_SPEC_PAYMENT_METHOD_ID'],
            amount: 200
        );

        if (!isset($response->elements)) {
            $this->markTestSkipped(
                message: 'Skipping test as response collection is null'
            );
        }

        $this->assertTrue(
            condition: $response->hasField(
                fieldName: 'applicant-government-id'
            )
        );
    }

    /**
     * Assert that the getFieldsByType method only returns fields of requested type
     *
     * @throws IllegalTypeException
     * @throws Exception
     */
    public function testApplicationDataSpecificationGetFieldsByType(): void
    {
        $response = Repository::getApplicationDataSpecification(
            storeId: $this->storeId,
            paymentMethodId: $_ENV['APPLICATION_DATA_SPEC_PAYMENT_METHOD_ID'],
            amount: 200
        );
        $headingFields = $response->getFieldsByType(type: Type::HEADING);

        if (!isset($response->elements)) {
            $this->markTestSkipped(
                message: 'Skipping test as response collection is null'
            );
        }

        $this->assertFalse(
            condition: $this->allFieldsOfType(
                fields: $response->elements,
                type: Type::HEADING
            )
        );
        $this->assertTrue(
            condition: $this->allFieldsOfType(
                fields: $headingFields,
                type: Type::HEADING
            )
        );
    }

    /**
     * Assert that the application_data_specification filter method works
     *
     * @throws IllegalTypeException
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testApplicationDataSpecificationFilter(): void
    {
        $response = Repository::getApplicationDataSpecification(
            storeId: $this->storeId,
            paymentMethodId: $_ENV['APPLICATION_DATA_SPEC_PAYMENT_METHOD_ID'],
            amount: 200
        );

        if (!isset($response->elements)) {
            $this->markTestSkipped(
                message: 'Skipping test as response collection is null'
            );
        }

        if (
            count($response->elements) > 1 &&
            $response->hasField(fieldName: 'applicant-government-id')
        ) {
            $filteredResponse = $response->filter(
                property: 'fieldName',
                fields: ['applicant-government-id']
            );

            $this->assertCount(
                expectedCount: count($response->elements) - 1,
                haystack: $filteredResponse->elements ?? new ApplicationFormSpecElementResponseCollection(
                    data: []
                )
            );
            $this->assertFalse(
                condition: $filteredResponse->hasField(
                    fieldName: 'applicant-government-id'
                )
            );
        } else {
            $this->markTestSkipped(
                message: 'Field required by test not found in response'
            );
        }
    }
}
