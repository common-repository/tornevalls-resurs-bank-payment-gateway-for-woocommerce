<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Network;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Network\Header;

/**
 * Tests for request Header model.
 */
class HeaderTest extends TestCase
{
    /**
     * Assert EmptyValueException is thrown when key is empty.
     */
    public function testValidateKeyThrowsWhenEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        new Header(key: '', value: 'value');
    }
}
