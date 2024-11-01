<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Callback;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Callback\Authorization;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Status;

/**
 * Test data integrity of Authorization callback model.
 */
class AuthorizationTest extends TestCase
{
    /**
     * Assert payment id cannot be empty.
     */
    public function testPaymentIdCannotByEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        new Authorization(
            paymentId: '',
            status: Status::AUTHORIZED,
            created: '2024-10-12 10:30'
        );
    }

    /**
     * Assert payment id must be uuid.
     *
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function testPaymentIdIsUuid(): void
    {
        $uuid = '12e3b939-2683-4b9c-a558-f4f6c7e18d99';

        $auth = new Authorization(
            paymentId: $uuid,
            status: Status::CAPTURED,
            created: '2020-10-12 10:09'
        );

        $this->assertSame(expected: $uuid, actual: $auth->paymentId);

        $this->expectException(exception: IllegalValueException::class);
        new Authorization(
            paymentId: 'some-value',
            status: Status::FROZEN,
            created: ''
        );
    }

    /**
     * Assert created cannot be empty.
     */
    public function testCreatedCannotByEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        new Authorization(
            paymentId: '12e3b939-2683-4b9c-a558-f4f6c7e18d99',
            status: Status::REJECTED,
            created: ''
        );
    }

    /**
     * Assert created is a date.
     *
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function testCreatedIsDate(): void
    {
        $date = '2022-07-04T10:12:34.840Z';

        $auth = new Authorization(
            paymentId: '12e3b939-2683-4b9c-a558-f4f6c7e18d99',
            status: Status::REJECTED,
            created: $date
        );

        $this->assertSame(expected: $date, actual: $auth->created);

        $this->expectException(exception: IllegalValueException::class);
        new Authorization(
            paymentId: '12e3b939-2683-4b9c-a558-f4f6c7e18d99',
            status: Status::REJECTED,
            created: 'not-a-date'
        );
    }
}
