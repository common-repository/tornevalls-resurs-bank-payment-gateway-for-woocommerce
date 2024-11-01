<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Network\Response;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Network\Response\Error;

/**
 * Tests for CURL error response model.
 */
class ErrorTest extends TestCase
{
    /**
     * Test model creation with valid property values.
     */
    public function testModel(): void
    {
        $error = new Error(
            traceId: 'a123j345k345a234234f3543534',
            message: 'something went wrong',
            code: 'SEEMS_WRONG',
            timestamp: '2025-02-25 10:00'
        );

        $this->assertInstanceOf(expected: Error::class, actual: $error);
    }

    /**
     * Test traceId property validation.
     */
    public function testTraceId(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Error(
            traceId: '',
            message: 'something went wrong',
            code: 'SEEMS_WRONG',
            timestamp: '2025-02-25 10:00'
        );
    }

    /**
     * Test message property validation.
     */
    public function testMessage(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Error(
            traceId: 'a123123g345345345345345345',
            message: '',
            code: 'SEEMS_WRONG',
            timestamp: '2025-02-25 10:00'
        );
    }

    /**
     * Test code property validation.
     */
    public function testCode(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Error(
            traceId: 'a123123g345345345345345345',
            message: 'Something is very wrong.',
            code: '',
            timestamp: '2025-02-25 10:00'
        );
    }

    /**
     * Test code property validation.
     */
    public function testTimestamp(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            new Error(
                traceId: 'a123123g345345345345345345',
                message: 'Something is very wrong.',
                code: 'SOME_CODE',
                timestamp: ''
            );

            $this->fail(
                message: 'Empty value accepted for property timestamp.'
            );
        } catch (EmptyValueException) {
            // This is expected.
        }

        try {
            new Error(
                traceId: 'a123123g345345345345345345',
                message: 'Something is very wrong.',
                code: 'AN_ERROR',
                timestamp: '1asd4sdf345ds'
            );

            $this->fail(
                message: 'Illegal value accepted for property timestamp.'
            );
        } catch (IllegalValueException) {
            // This is expected.
        }
    }
}
