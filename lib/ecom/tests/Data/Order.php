<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data;

use JsonException;
use Resursbank\Ecom\Exception\TestException;
use stdClass;

/**
 * Mock data for tests relating to Payment module, Create Payment
 * implementation.
 *
 * @todo Add more data to this class.
 */
class Order
{
    public static string $data = <<<EOD
{
    "actionLog": [
        {
            "id": "160d2b10-7586-4a32-87a1-23a425b252ce",
            "type": "CREATE",
            "created": "2022-09-07T14:52:56.709",
            "creator": "jultomten",
            "orderLines": [
                {
                    "description": "Product one",
                    "quantity": 1.00000,
                    "reference": "TST-101",
                    "type": "NORMAL",
                    "quantityUnit": "st",
                    "unitAmountIncludingVat": 625.00000,
                    "vatRate": 25.00000,
                    "totalAmountIncludingVat": 625.00000,
                    "totalVatAmount": 125.00000
                },
                {
                    "description": "Product one",
                    "quantity": 1.00000,
                    "reference": "TST-101",
                    "type": "NORMAL",
                    "quantityUnit": "st",
                    "unitAmountIncludingVat": 625.00000,
                    "vatRate": 25.00000,
                    "totalAmountIncludingVat": 625.00000,
                    "totalVatAmount": 125.00000
                },
                {
                    "description": "Discount one",
                    "quantity": 1.00000,
                    "reference": "DC-101",
                    "type": "NORMAL",
                    "quantityUnit": "st",
                    "unitAmountIncludingVat": -25.00000,
                    "vatRate": 0.00000,
                    "totalAmountIncludingVat": -25.00000,
                    "totalVatAmount": 0.00000
                }
            ]
        }
    ],
    "orderReference": "aklsfjah234oiaslhjfd",
    "possibleActions": [],
    "totalOrderAmount": 12.34,
    "canceledAmount": 0.12,
    "capturedAmount": 1.23,
    "refundedAmount": 0.0
}
EOD;

    /**
     * @throws JsonException
     * @throws TestException
     */
    public static function getData(): stdClass
    {
        $data = json_decode(
            json: self::$data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        if (!$data instanceof stdClass) {
            throw new TestException(message: '$data is not a valid stdClass.');
        }

        return $data;
    }
}
