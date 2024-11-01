<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Network\Auth\Jwt;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt\Token;

/**
 * Tests for JWT token.
 */
class TokenTest extends TestCase
{
    /**
     * Assert EmptyValueException is thrown when accessToken is empty.
     */
    public function testValidateAccessTokenThrowsOnEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Token(access_token: '', token_type: 'Bearer', expires_in: 0);
    }

    /**
     * Assert EmptyValueException is thrown when token type is empty.
     */
    public function testValidateTokenTypeThrowsOnEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Token(access_token: 'foo', token_type: '', expires_in: 0);
    }

    /**
     * Assert current timestamp is automatically appended to validUntil prop.
     *
     * @throws EmptyValueException
     */
    public function testExpiresInAppendsTimestamp(): void
    {
        $token = new Token(
            access_token: 'foo',
            token_type: 'Bearer',
            expires_in: 0
        );

        self::assertSame(
            expected: time(),
            actual: $token->expires_in
        );
    }
}
