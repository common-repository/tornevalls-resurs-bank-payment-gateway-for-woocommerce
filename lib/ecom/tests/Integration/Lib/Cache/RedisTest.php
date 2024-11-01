<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Cache;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use Redis as Server;
use RedisException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Cache\Redis;

/**
 * Assert the Redis cache implementation works as expected.
 */
class RedisTest extends TestCase
{
    private Redis $redis;

    private string $key;

    /**
     * Setup filesystem cache instance.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->redis = new Redis(host: $_ENV['REDIS_HOST']);
        $this->key = $this->getKey();

        Config::setup(cache: $this->redis);

        parent::setUp();
    }

    /**
     * @throws Exception
     */
    private function getKey(): string
    {
        // NOTE: Simply using time() is unsafe, tests run too quickly.
        return AbstractCache::getKey(
            key: 'redis-cache-' . random_int(min: 0, max: 999999999) . time()
        );
    }

    /**
     * @throws RedisException
     * @SuppressWarnings(PHPMD.MissingImport)
     */
    private function getRedisConnection(): Server
    {
        $server = new Server();
        $server->connect(host: $_ENV['REDIS_HOST']);

        return $server;
    }

    /**
     * Assert that method write() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @throws RedisException
     * @throws ValidationException
     * @throws JsonException
     */
    public function testWriteThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->write(
            key: 'It will not take spaces',
            data: '{cool:running}',
            ttl: 5
        );
    }

    /**
     * Assert ValidationException occurs when calling write() with an empty key.
     *
     * @throws RedisException
     * @throws ValidationException
     * @throws JsonException
     */
    public function testWriteThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->write(key: '', data: 'StutterHat', ttl: 12);
    }

    /**
     * Assert that write() will pass without failure.
     *
     * @throws ValidationException
     * @throws RedisException
     * @throws JsonException
     * @throws ConfigException
     */
    public function testWrite(): void
    {
        $data = 'basic data';

        $this->redis->write(key: $this->key, data: $data, ttl: 9999);

        $this->assertEquals(
            expected: $data,
            actual: $this->redis->read(key: $this->key)
        );
    }

    /**
     * Assert that method read() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @throws RedisException
     * @throws ValidationException
     * @throws ConfigException
     */
    public function testReadThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->read(key: '?');
    }

    /**
     * Assert ValidationException occurs when calling read() with an empty key.
     *
     * @throws RedisException
     * @throws ValidationException
     * @throws ConfigException
     */
    public function testReadThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->read(key: '');
    }

    /**
     * Assert that read() method returns NULL if no valid data was found.
     *
     * @throws RedisException
     * @throws ValidationException
     * @throws ConfigException
     */
    public function testReadReturnsNullForUndefinedData(): void
    {
        $this->assertNull(actual: $this->redis->read(key: $this->key));
    }

    /**
     * Assert that read() can fetch data from Redis.
     *
     * @throws ValidationException
     * @throws RedisException
     * @throws ConfigException|JsonException
     */
    public function testRead(): void
    {
        $data = '9891823918391094850834523';

        $this->redis->write(key: $this->key, data: $data, ttl: 9999);

        $this->assertEquals(
            expected: $data,
            actual: $this->redis->read(key: $this->key)
        );
    }

    /**
     * Assert that read() returns NULL when cached data has expired.
     *
     * @throws RedisException
     * @throws ValidationException
     * @throws ConfigException
     * @throws JsonException
     */
    public function testReadReturnsNullForStaleData(): void
    {
        $data = 'testing a test';

        $this->redis->write(key: $this->key, data: $data, ttl: 1);

        $this->assertSame(
            expected: $data,
            actual: $this->redis->read(key: $this->key)
        );

        sleep(seconds: 2);

        $this->assertNull(actual: $this->redis->read(key: $this->key));
    }

    /**
     * Assert that method clear() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @throws RedisException
     * @throws ValidationException
     */
    public function testClearThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->clear(key: 'HaHa#');
    }

    /**
     * Assert ValidationException occurs when calling clear() with an empty key.
     *
     * @throws RedisException
     * @throws ValidationException
     */
    public function testClearThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->clear(key: '');
    }

    /**
     * Assert that clear() will delete data from Redis.
     *
     * @throws ValidationException
     * @throws RedisException
     */
    public function testClear(): void
    {
        $data = 'Some crazy set of data.';

        $conn = $this->getRedisConnection();

        $conn->set(key: $this->key, value: $data);

        $this->assertEquals(
            expected: $data,
            actual: $conn->get(key: $this->key)
        );

        $this->redis->clear(key: $this->key);

        $this->assertFalse(condition: $conn->get(key: $this->key));
    }

    /**
     * Assert read() returns NULL when cache is invalidated, and cached data
     * when not invalidated.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testCacheInvalidation(): void
    {
        $key1 = $this->getKey();
        $key2 = $this->getKey();
        $data1 = 'Hello!';
        $data2 = 'Big Bird';

        $this->redis->write(key: $key1, data: $data1, ttl: 10000);
        $this->assertSame(
            expected: $data1,
            actual: $this->redis->read(key: $key1)
        );

        $this->redis->invalidate();
        $this->assertNull(
            actual: $this->redis->read(key: $key1)
        );

        // createdAt of new Entry must be younger than invalidation marker.
        sleep(seconds: 1);

        $this->redis->write(key: $key2, data: $data2, ttl: 10000);
        $this->assertSame(
            expected: $data2,
            actual: $this->redis->read(key: $key2)
        );

        // Update invalidation marker, invalidating the entry we just created.
        sleep(seconds: 1);

        $this->redis->invalidate();
        $this->assertNull(actual: $this->redis->read(key: $key2));

        // Delete the invalidation marker, once again validating current cache.
        $this->redis->clear(
            key: AbstractCache::getKey(
                key: AbstractCache::CACHE_INVALIDATION_KEY
            )
        );

        $this->assertSame(
            expected: $data1,
            actual: $this->redis->read(key: $key1)
        );
        $this->assertSame(
            expected: $data2,
            actual: $this->redis->read(key: $key2)
        );
    }
}
