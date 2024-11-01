<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Utilities;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\SessionException;
use Resursbank\Ecom\Exception\SessionValueException;
use Resursbank\Ecom\Lib\Utilities\Session;
use Resursbank\EcomTest\Utilities\MockSessionTrait;

/**
 * Session testing.
 */
class SessionTest extends TestCase
{
    use MockSessionTrait;

    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        $this->setupSession(test: $this);

        parent::setUp();
    }

    /**
     * Assert set() assigns value to PHP session.
     *
     * @throws SessionException
     */
    public function testSet(): void
    {
        $key = 'monkey';
        $val = 'island';

        $this->enableSession();

        $this->session->set(key: $key, val: $val);

        $sessionKey = $this->session->getKey(key: $key);

        $this->assertTrue(condition: isset($_SESSION));
        $this->assertArrayHasKey(key: $sessionKey, array: $_SESSION);

        if (!isset($_SESSION[$sessionKey])) {
            $this->fail(message: "$sessionKey not set in session.");
        }

        $this->assertSame(expected: $val, actual: $_SESSION[$sessionKey]);
    }

    /**
     * Asset set() throws SessionException if session is not active.
     *
     * @throws SessionException
     */
    public function testSetThrowsWithoutSession(): void
    {
        $this->disableSession();

        $this->expectException(exception: SessionException::class);

        $this->session->set(key: '55', val: 'Bottles');
    }

    /**
     * Asset get() throws SessionException if session is not active.
     *
     * @throws SessionException
     */
    public function testGetThrowsWithoutSession(): void
    {
        $this->disableSession();

        $this->expectException(exception: SessionException::class);

        $this->session->get(key: 'gamble');
    }

    /**
     * Asset get() throws SessionValueException if key is not set in session.
     *
     * @throws SessionException
     */
    public function testGetThrowsWithoutKey(): void
    {
        $this->enableSession();

        $this->expectException(exception: SessionValueException::class);
        $this->expectExceptionCode(code: 404);

        $this->session->get(key: 'CheeseSauce');
    }

    /**
     * Asset get() throws SessionValueException if value of key is not a string.
     *
     * @throws SessionException
     */
    public function testGetThrowsWhenKeyNotString(): void
    {
        $key = 'wizard';
        $val = 5;

        $this->enableSession();

        $_SESSION[$this->session->getKey(key: $key)] = $val;

        $this->expectException(exception: SessionValueException::class);
        $this->expectExceptionCode(code: 415);

        $this->session->get(key: $key);
    }

    /**
     * Asset get() returns value from session.
     *
     * @throws SessionException
     */
    public function testGet(): void
    {
        $key = 'wizard';
        $val = '{"thumping":"good-one"}';

        $this->enableSession();

        $this->session->set(key: $key, val: $val);

        $this->assertSame(
            expected: $val,
            actual: $this->session->get(key: $key)
        );
    }

    /**
     * Assert getKey() prefixes keys.
     */
    public function testGetKey(): void
    {
        $key = 'old';

        $this->assertSame(
            expected: Session::PREFIX . $key,
            actual: $this->session->getKey(key: $key)
        );
    }
}
