<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Utilities\Strings;

use function in_array;

/**
 * Tests for PaymentMethod functionality
 */
class PaymentMethodTest extends TestCase
{
    /**
     * Generate a dummy payment method with specified type
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    private function generatePaymentMethodWithType(Type $type): PaymentMethod
    {
        return new PaymentMethod(
            id: Strings::getUuid(),
            name: Strings::getUuid(),
            type: $type,
            minPurchaseLimit: 1,
            maxPurchaseLimit: 1000,
            minApplicationLimit: 1,
            maxApplicationLimit: 1000,
            legalLinks: new PaymentMethod\LegalLinkCollection(data: []),
            enabledForLegalCustomer: false,
            enabledForNaturalCustomer: true,
            priceSignagePossible: true,
            sortOrder: 1
        );
    }

    /**
     * Assert that isPartPayment gives correct responses depending on the method's type
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsPartPayment(): void
    {
        $validCases = [
            Type::RESURS_REVOLVING_CREDIT,
            Type::RESURS_PART_PAYMENT,
            Type::RESURS_CARD
        ];

        foreach (Type::cases() as $case) {
            $method = $this->generatePaymentMethodWithType(type: $case);

            if (in_array(needle: $case, haystack: $validCases, strict: true)) {
                $this->assertTrue(condition: $method->isPartPayment());
                continue;
            }

            $this->assertFalse(condition: $method->isPartPayment());
        }
    }
}
