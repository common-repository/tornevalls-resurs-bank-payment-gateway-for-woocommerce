<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Network\Auth;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;

/**
 * Tests for JWT credentials model.
 */
class JwtTest extends TestCase
{
    /**
     * Assert EmptyValueException is thrown when clientId is empty.
     */
    public function testThrowsOnEmptyClientId(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Jwt(
            clientId: '',
            clientSecret: 'secret',
            scope: Scope::MOCK_MERCHANT_API,
            grantType: GrantType::CREDENTIALS
        );
    }

    /**
     * Assert EmptyValueException is thrown when clientSecret is empty.
     */
    public function testThrowsOnEmptyClientSecret(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Jwt(
            clientId: 'clientId',
            clientSecret: '',
            scope: Scope::MOCK_MERCHANT_API,
            grantType: GrantType::CREDENTIALS
        );
    }
}
