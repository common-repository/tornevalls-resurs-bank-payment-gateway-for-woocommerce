<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentMethod\Models\PaymentMethod;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\PaymentMethod\LegalLink;
use Resursbank\Ecom\Lib\Order\PaymentMethod\LegalLink\Type;

/**
 * Test data integrity of legal link object attached to payment methods.
 */
class LegalLinkTest extends TestCase
{
    /**
     * Assert that a legal link can't be created with an empty url supplied.
     */
    public function testValidateUrlThrowsWithEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        new LegalLink(url: '', type: Type::GENERAL_TERMS, appendAmount: false);
    }
}
