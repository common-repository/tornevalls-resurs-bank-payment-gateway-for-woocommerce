<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Cache;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Model\Cache\Entry;
use stdClass;

/**
 * This class will test general cache methods.
 */
class AbstractCacheTest extends TestCase
{
    /**
     * Test unique instance of AbstractCache class (mocked).
     */
    private AbstractCache $cache;

    /**
     * Test unique cache key.
     */
    private string $key;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->cache = $this->getMockForAbstractClass(
            originalClassName: AbstractCache::class
        );

        $this->key = $this->getKey();

        parent::setUp();
    }

    /**
     * @throws Exception
     */
    private function getKey(): string
    {
        return
            AbstractCache::CACHE_KEY_PREFIX .
            'test' .
            random_int(min: 0, max: 999999)
        ;
    }

    /**
     * Assert that a key containing a mixture of upper-, lowercase, hyphens and
     * underscores pass validation.
     *
     * @throws ValidationException
     */
    public function testValidationPass(): void
    {
        $this->cache->validateKey(key: $this->key);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Assert that keys containing illegal chars will cause ValidationException.
     *
     * @throws ValidationException
     */
    public function testValidationFailsWithIllegalChars(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->validateKey(key: "$this->key!!");
    }

    /**
     * Assert that empty keys will cause ValidationException.
     *
     * @throws ValidationException
     */
    public function testValidationFailsWithEmpty(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->validateKey(key: '');
    }

    /**
     * Assert that empty keys will cause ValidationException.
     *
     * @throws ValidationException
     */
    public function testValidationFailsWithoutPrefix(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->validateKey(key: 'some-key');
    }

    /**
     * Assert the getKey() method results in a prefixed cache key.
     */
    public function testGetKeyReturnsPrefixedKey(): void
    {
        $this->assertSame(
            expected: AbstractCache::CACHE_KEY_PREFIX . 'test-key',
            actual: AbstractCache::getKey(key: 'test-key')
        );
    }

    /**
     * Assert encodeData() returns JSON encoded instance of Entry object.
     *
     * @throws JsonException
     */
    public function testEncodeData(): void
    {
        $data = 'Hello there, this is some text.';
        $ttl = 100;

        $raw = $this->cache->encodeEntry(data: $data, ttl: $ttl);

        $this->assertJson(actualJson: $raw);

        $entry = json_decode(
            json: $raw,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        $this->assertInstanceOf(expected: stdClass::class, actual: $entry);
        $this->assertSame(expected: $data, actual: $entry->data);
        $this->assertSame(expected: $ttl, actual: $entry->ttl);
    }

    /**
     * Assert decodeData() decodes Entry object which has been JSON encoded.
     *
     * @throws JsonException
     */
    public function testDecodeData(): void
    {
        $data = '{ "i": "am", "a": "json", "object": 5, "or": false }';
        $ttl = 123474;

        $raw = $this->cache->encodeEntry(data: $data, ttl: $ttl);

        $this->assertJson(actualJson: $raw);

        $entry = $this->cache->decodeEntry(data: $raw);

        $this->assertInstanceOf(expected: Entry::class, actual: $entry);
        $this->assertSame(expected: $data, actual: $entry->data);
        $this->assertSame(expected: $ttl, actual: $entry->ttl);
    }

    /**
     * Assert decodeData() method will return NULL for invalid data.
     */
    public function testDecodeDataReturnsNull(): void
    {
        $this->assertNull(actual: $this->cache->decodeEntry(data: ''));
        $this->assertNull(actual: $this->cache->decodeEntry(data: 'not-json'));
        $this->assertNull(actual: $this->cache->decodeEntry(
            data: '{ "data": "Some data", "createdAt": ' . time() . ' }'
        ));
    }
}
