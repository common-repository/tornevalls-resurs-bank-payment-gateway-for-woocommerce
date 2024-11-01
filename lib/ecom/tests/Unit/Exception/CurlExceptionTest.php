<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Lib\Model\Network\Response\Error;

/**
 * Verifies that the CurlException class works as intended.
 */
class CurlExceptionTest extends TestCase
{
    /**
     * Test that getError method will return instance of Error when possible,
     * and NULL otherwise.
     */
    public function testGetError(): void
    {
        $boolBody = new CurlException(
            message: 'Nothing',
            code: 450,
            body: false
        );

        $notJsonBody = new CurlException(
            message: 'Nothing',
            code: 450,
            body: 'not-valid-json'
        );

        $invalidJsonBody = new CurlException(
            message: 'Nothing',
            code: 450,
            body: '{"traceId": "a2345s45sdf4sdf3wdf", "message": "some error message", "cookie": "Please"}'
        );

        $validJsonBody = new CurlException(
            message: 'Nothing',
            code: 450,
            body: '{"traceId": "a2345s45sdf4sdf3wdf", "message": ' .
                '"some error message", "code": "SOME_CODE", "timestamp": "2023-12-10 18:55:12"}'
        );

        $this->assertNull(actual: $boolBody->getError());
        $this->assertNull(actual: $notJsonBody->getError());
        $this->assertNull(actual: $invalidJsonBody->getError());
        $this->assertInstanceOf(
            expected: Error::class,
            actual: $validJsonBody->getError()
        );
    }
}
