<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Utilities;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Utilities\Session;

/**
 * Methods to spawn a mocked session handler.
 */
trait MockSessionTrait
{
    private MockObject&Session $session;

    /**
     * PHPUnit sends headers before this executes, thus we cannot manipulate
     * our session handler (starting / stopping it to check the behaviour
     * of our methods). We mock the isAvailable method to fix that.
     */
    public function setupSession(
        TestCase $test
    ): void {
        // Clear session data.
        unset($_SESSION);

        $this->session = $test->createPartialMock(
            originalClassName: Session::class,
            methods: ['isAvailable']
        );
    }

    /**
     * Make session appear enabled.
     *
     * @noinspection UnnecessaryAssertionInspection
     */
    public function enableSession(): void
    {
        $this->session
            ->expects($this->any())
            ->method(constraint: 'isAvailable')
            ->willReturn(value: true);
    }

    /**
     * Make session appear disabled.
     *
     * @noinspection UnnecessaryAssertionInspection
     */
    public function disableSession(): void
    {
        $this->session
            ->expects($this->any())
            ->method(constraint: 'isAvailable')
            ->willReturn(value: false);
    }
}
