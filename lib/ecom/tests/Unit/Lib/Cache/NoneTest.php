<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Cache;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Cache\None;

/**
 * Assert the None cache driver works as expected.
 */
class NoneTest extends TestCase
{
    private None $cache;

    private string $key;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->cache = new None();
        $this->key = $this->getKey();

        parent::setUp();
    }

    /**
     * @throws Exception
     */
    private function getKey(): string
    {
        // NOTE: Simply using time() is unsafe, tests run too quickly.
        return AbstractCache::getKey(
            key: 'none-cache-' . random_int(min: 0, max: 999999999) . time()
        );
    }

    /**
     * Assert that method read() throws instance of ValidationException if our
     * key contains illegal characters.
     */
    public function testReadThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->read(key: $this->key . '#');
    }

    /**
     * Assert ValidationException occurs when calling read() with an empty key.
     */
    public function testReadThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->read(key: '');
    }

    /**
     * Assert that read() method will always return null.
     *
     * @throws ValidationException
     */
    public function testReadReturnsNull(): void
    {
        $this->assertNull(actual: $this->cache->read(key: $this->key));
    }

    /**
     * Assert that method write() throws instance of ValidationException if our
     * key contains illegal characters.
     */
    public function testWriteThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->write(
            key: $this->key . '?',
            data: 'Potato plats grow in June',
            ttl: 12556
        );
    }

    /**
     * Assert ValidationException occurs when calling write() with an empty key.
     */
    public function testWriteThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->write(key: '', data: 'Stutter hat', ttl: 8723847);
    }

    /**
     * Assert that write() method is callable but doesn't do anything.
     *
     * @throws ValidationException
     */
    public function testWriteDoesNothing(): void
    {
        $this->cache->write(key: $this->key, data: 'anything', ttl: 9999);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Assert that method clear() throws instance of ValidationException if our
     * key contains illegal characters.
     */
    public function testClearThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->clear(key: $this->key . '$');
    }

    /**
     * Assert ValidationException occurs when calling clear() with an empty key.
     */
    public function testClearThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->cache->clear(key: '');
    }

    /**
     * Assert that clear() method is callable but doesn't do anything.
     *
     * @throws ValidationException
     */
    public function testClearDoesNothing(): void
    {
        $this->cache->clear(key: $this->key);
        $this->expectNotToPerformAssertions();
    }
}
