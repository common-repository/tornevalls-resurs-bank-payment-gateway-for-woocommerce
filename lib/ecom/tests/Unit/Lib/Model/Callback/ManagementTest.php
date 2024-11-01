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
use Resursbank\Ecom\Lib\Model\Callback\Enum\Action;
use Resursbank\Ecom\Lib\Model\Callback\Management;

/**
 * Test data integrity of Management callback model.
 */
class ManagementTest extends TestCase
{
    /**
     * Assert payment id cannot be empty.
     */
    public function testPaymentIdCannotByEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        new Management(
            paymentId: '',
            action: Action::CAPTURE,
            actionId: '5fab5f64-5c17-4b62-b3fc-3c963f66afa7',
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

        $auth = new Management(
            paymentId: $uuid,
            action: Action::REFUND,
            actionId: '5fab5f64-5c17-4b62-b3fc-3c963f66afa7',
            created: '2020-10-12 10:09'
        );

        $this->assertSame(expected: $uuid, actual: $auth->paymentId);

        $this->expectException(exception: IllegalValueException::class);
        new Management(
            paymentId: 'some-value',
            action: Action::CAPTURE,
            actionId: '5fab5f64-5c17-4b62-b3fc-3c963f66afa7',
            created: ''
        );
    }

    /**
     * Assert action id cannot be empty.
     */
    public function testActionIdCannotByEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        new Management(
            paymentId: '',
            action: Action::CAPTURE,
            actionId: '5fab5f64-5c17-4b62-b3fc-3c963f66afa7',
            created: '2024-10-12 10:30'
        );
    }

    /**
     * Assert action id must be uuid.
     *
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function testActionIdIsUuid(): void
    {
        $uuid = '12e3b939-2683-4b9c-a558-f4f6c7e18d99';

        $auth = new Management(
            paymentId: '5fab5f64-5c17-4b62-b3fc-3c963f66afa7',
            action: Action::REFUND,
            actionId: $uuid,
            created: '2020-10-12 10:09'
        );

        $this->assertSame(expected: $uuid, actual: $auth->actionId);

        $this->expectException(exception: IllegalValueException::class);
        new Management(
            paymentId: 'some-value',
            action: Action::CAPTURE,
            actionId: '5fab5f64-5c17-4b62-b3fc-3c963f66afa7',
            created: ''
        );
    }

    /**
     * Assert created cannot be empty.
     */
    public function testCreatedCannotByEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        new Management(
            paymentId: '12e3b939-2683-4b9c-a558-f4f6c7e18d99',
            action: Action::CANCEL,
            actionId: '5fab5f64-5c17-4b62-b3fc-3c963f66afa7',
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

        $auth = new Management(
            paymentId: '12e3b939-2683-4b9c-a558-f4f6c7e18d99',
            action: Action::CANCEL,
            actionId: '5fab5f64-5c17-4b62-b3fc-3c963f66afa7',
            created: $date
        );

        $this->assertSame(expected: $date, actual: $auth->created);

        $this->expectException(exception: IllegalValueException::class);
        new Management(
            paymentId: '12e3b939-2683-4b9c-a558-f4f6c7e18d99',
            action: Action::CANCEL,
            actionId: '5fab5f64-5c17-4a62-b3fc-3c963f66afa6',
            created: 'not-a-date'
        );
    }
}
