<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Cache;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Model\Cache\Entry as EntryModel;

/**
 * Tests for request Entry model.
 */
class Entry extends TestCase
{
    /**
     * Assert EmptyValueException is thrown when key is empty.
     */
    public function testGetExpirationTime(): void
    {
        $createdAt = time();
        $ttl = 9349857341;

        $entry = new EntryModel(data: 'Some', ttl: $ttl, createdAt: $createdAt);

        $this->assertSame(
            expected: $createdAt + $ttl,
            actual: $entry->getExpirationTime()
        );
    }
}
