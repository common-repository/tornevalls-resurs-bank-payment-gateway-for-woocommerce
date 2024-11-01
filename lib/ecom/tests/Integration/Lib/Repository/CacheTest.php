<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Repository;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\CollectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\EcomTest\Data\Models\Music;
use Resursbank\EcomTest\Data\Models\MusicCollection;

/**
 * Verifies that the Cache class works as intended.
 */
final class CacheTest extends TestCase
{
    private const CACHE_PATH = '/tmp/ecom-test/repository/cache';

    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(path: self::CACHE_PATH)
        );

        parent::setUp();
    }

    private function getCache(
        string $key = 'music-cache',
        int $ttl = 3600
    ): Cache {
        return new Cache(key: $key, model: Music::class, ttl: $ttl);
    }

    /**
     * Assert write() writes the data to the cache.
     *
     * @throws CacheException
     * @throws ConfigException
     */
    public function testWriteModel(): void
    {
        $cache = $this->getCache();
        $cache->write(data: new Music(id: 1, genre: 'music'));

        /** @var Music $data */
        $data = $cache->read();

        $this->assertInstanceOf(expected: Music::class, actual: $data);
        $this->assertSame(expected: 'music', actual: $data->genre);
    }

    /**
     * Assert write() writes the data to the cache.
     *
     * @throws CacheException
     * @throws CollectionException
     * @throws ConfigException
     * @throws IllegalTypeException
     */
    public function testWriteCollection(): void
    {
        $cache = $this->getCache();
        $cache->write(data: new MusicCollection(data: [
            new Music(id: 1, genre: 'funk'),
            new Music(id: 2, genre: 'techno'),
            new Music(id: 3, genre: 'rock'),
            new Music(id: 4, genre: 'trance'),
        ]));

        /** @var MusicCollection $data */
        $data = $cache->read();

        $this->assertInstanceOf(
            expected: MusicCollection::class,
            actual: $data
        );

        /** @var Music $music */
        $music = $data->current();

        $this->assertInstanceOf(expected: Music::class, actual: $music);

        $this->assertSame(expected: 'funk', actual: $music->genre);

        $this->assertCount(expectedCount: 4, haystack: $data);
    }

    /**
     * Assert read() returns null when ttl is expired.
     *
     * @throws CacheException
     * @throws ConfigException
     */
    public function testExpiredTtlReturnsNull(): void
    {
        $cache = $this->getCache(ttl: 1);
        $cache->write(data: new Music(id: 1, genre: 'funk'));

        /** @var Music $data */
        $data = $cache->read();

        $this->assertInstanceOf(expected: Music::class, actual: $data);
        $this->assertSame(expected: 'funk', actual: $data->genre);

        sleep(seconds: 2);

        $data = $cache->read();
        $this->assertNull(actual: $data);
    }

    /**
     * Assert clear() clears the cache.
     *
     * @throws CacheException
     * @throws ConfigException
     */
    public function testClearCache(): void
    {
        $cache = $this->getCache();
        $cache->write(data: new Music(id: 1, genre: 'funk'));
        $data = $cache->read();
        $this->assertInstanceOf(expected: Music::class, actual: $data);
        $cache->clear();
        $data = $cache->read();
        $this->assertNull(actual: $data);
    }

    /**
     * Assert cache is separated by key.
     *
     * @throws CacheException
     * @throws ConfigException
     */
    public function testCacheSeparatesByKet(): void
    {
        $music1 = new Music(id: 1, genre: 'funk');
        $music2 = new Music(id: 2, genre: 'techno');

        $cache1 = $this->getCache(key: 'music-cache-1');
        $cache2 = $this->getCache(key: 'music-cache-2');
        $cache1->write(data: $music1);
        $cache2->write(data: $music2);
        $data1 = $cache1->read();
        $data2 = $cache2->read();
        $this->assertInstanceOf(expected: Music::class, actual: $data1);
        $this->assertInstanceOf(expected: Music::class, actual: $data2);
        $this->assertEquals(expected: $music1, actual: $data1);
        $this->assertEquals(expected: $music2, actual: $data2);
        $this->assertNotEquals(expected: $data1, actual: $data2);
    }
}
