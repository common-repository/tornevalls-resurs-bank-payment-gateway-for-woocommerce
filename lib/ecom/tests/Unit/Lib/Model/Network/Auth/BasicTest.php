<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Network\Auth;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Network\Auth\Basic;

/**
 * Tests for Basic credentials model.
 */
class BasicTest extends TestCase
{
    /**
     * Assert EmptyValueException is thrown when username is empty.
     */
    public function testEmptyUsernameThrows(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        new Basic(username: '', password: 'password');
    }

    /**
     * Assert EmptyValueException is thrown when password is empty.
     */
    public function testEmptyPasswordThrows(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        new Basic(username: 'username', password: '');
    }
}
