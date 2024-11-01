<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Repository;

use JsonException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\CollectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\EcomTest\Data\Models\Instrument;
use Resursbank\EcomTest\Data\Models\InstrumentCollection;
use Resursbank\EcomTest\Data\Models\Music;
use Resursbank\EcomTest\Data\Models\MusicCollection;
use stdClass;

use function is_string;

/**
 * Verifies business logic of ModelConverter trait.
 */
final class CacheTest extends TestCase
{
    private MockObject&None $cacheDriver;

    /**
     * We call the actual Config::setup() method to initiate mocked objects
     * to be utilised in tests against the static methods available on our
     * subject class. The methods on our subject class (such as readCache())
     * will make calls to object such as Config::getCache(), and we wish
     * to test behaviour when the results from the API / Cache differ.
     */
    protected function setUp(): void
    {
        $this->cacheDriver = $this->createMock(originalClassName: None::class);

        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: $this->cacheDriver
        );

        parent::setUp();
    }

    /**
     * Get instance of Cache repository.
     */
    private function getCache(): Cache
    {
        return new Cache(key: 'test', model: Music::class, ttl: 3600);
    }

    /**
     * Helper method to assign result from Config::getCache()->read()
     *
     * @throws JsonException
     */
    private function setCacheReadReturn(
        mixed $data
    ): void {
        if (!is_string(value: $data)) {
            $data = json_encode(value: $data, flags: JSON_THROW_ON_ERROR);
        }

        $this->cacheDriver->method('read')->willReturn(value: $data);
    }

    /**
     * Assert that read() returns NULL without any data.
     *
     * @throws CacheException
     * @throws ConfigException
     */
    public function testReadReturnsNull(): void
    {
        $this->assertNull(actual: $this->getCache()->read());
    }

    /**
     * Assert read() returns NULL if cache is an empty array.
     *
     * @throws CacheException
     * @throws ConfigException
     * @throws JsonException
     */
    public function testReadReturnsNullWithEmptyArray(): void
    {
        $this->setCacheReadReturn(data: []);
        $this->assertNull(actual: $this->getCache()->read());
    }

    /**
     * Assert read() throws CacheException when cache is invalid JSON encoded
     * data.
     *
     * @throws CacheException
     * @throws ConfigException
     * @throws JsonException
     */
    public function testReadThrowsCacheExceptionForInvalidJson(): void
    {
        $this->expectException(exception: CacheException::class);
        $this->setCacheReadReturn(data: json_encode(
            value: 'invalid json',
            flags: JSON_THROW_ON_ERROR
        ));
        $this->getCache()->read();
    }

    /**
     * Assert read() throws CacheException when cache isn't JSON encoded data.
     *
     * @throws CacheException
     * @throws ConfigException
     * @throws JsonException
     */
    public function testReadThrowsCacheExceptionWithoutJson(): void
    {
        $this->expectException(exception: CacheException::class);
        $this->setCacheReadReturn(data: 'This is not json');
        $this->getCache()->read();
    }

    /**
     * Assert read() converts stdClass to Model.
     *
     * @throws CacheException
     * @throws ConfigException
     * @throws JsonException
     */
    public function testReadConvertsModel(): void
    {
        $data = new stdClass();
        $data->id = 1;
        $data->genre = 'test';

        $this->setCacheReadReturn(data: $data);

        $this->assertInstanceOf(
            expected: Music::class,
            actual: $this->getCache()->read()
        );
    }

    /**
     * Assert read() converts array to Collection.
     *
     * @throws CacheException
     * @throws JsonException
     * @throws CollectionException
     * @throws ConfigException
     */
    public function testReadConvertsCollection(): void
    {
        $data1 = new Music(id: 1, genre: 'funk');
        $data2 = new Music(id: 2, genre: 'rock');

        $this->setCacheReadReturn(data: [$data1, $data2]);

        /** @var MusicCollection $data */
        $data = $this->getCache()->read();

        $this->assertInstanceOf(
            expected: MusicCollection::class,
            actual: $data
        );
        $this->assertCount(expectedCount: 2, haystack: $data);

        $current = $data->current();

        $this->assertInstanceOf(expected: Music::class, actual: $current);
        $this->assertSame(expected: 1, actual: $current->id);
    }

    /**
     * Assert write() throws CacheException when passed a Model instance not
     * matching the model class of the Cache instance (see getCache()).
     *
     * @throws CacheException
     */
    public function testWriteThrowsWithInvalidModel(): void
    {
        $this->expectException(exception: CacheException::class);
        $this->getCache()->write(data: new Instrument(id: 1, name: 'guitar'));
    }

    /**
     * Assert write() throws CacheException when passed a Collection instance
     * not matching the model class of the Cache instance (see getCache()).
     *
     * @throws CacheException
     * @throws IllegalTypeException
     */
    public function testWriteThrowsWithInvalidCollection(): void
    {
        $this->expectException(exception: CacheException::class);
        $this->getCache()->write(data: new InstrumentCollection(data: [
            new Instrument(id: 1, name: 'guitar'),
            new Instrument(id: 1, name: 'guitar'),
            new Instrument(id: 1, name: 'guitar'),
            new Instrument(id: 1, name: 'guitar'),
        ]));
    }
}
