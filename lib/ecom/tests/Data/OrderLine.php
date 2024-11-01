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

use function is_array;
use function is_int;

/**
 * Mock data for tests relating to Payment module, Create Payment
 * implementation.
 *
 * @todo Add more data to this class.
 */
class OrderLine
{
    public static string $data = <<<EOD
[
    {
        "description": "Bok",
        "quantity": 2.00000,
        "reference": "T-800",
        "type": "PHYSICAL_GOODS",
        "quantityUnit": "st",
        "unitAmountIncludingVat": 150.75000,
        "vatRate": 25.00000,
        "totalAmountIncludingVat": 301.50000,
        "totalVatAmount": 60.30000
    },
    {
        "description": "Album",
        "quantity": 1.00000,
        "reference": "ALBUM-012G-VV",
        "type": "DIGITAL_GOODS",
        "quantityUnit": "st",
        "unitAmountIncludingVat": 120.00000,
        "vatRate": 25.00000,
        "totalAmountIncludingVat": 199.90000,
        "totalVatAmount": 79.90000
    }
]
EOD;

    /**
     * @throws JsonException
     * @throws TestException
     */
    public static function getRandomData(): stdClass
    {
        $data = json_decode(
            json: self::$data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        if (!is_array(value: $data)) {
            throw new TestException(
                message: 'Failed to decode JSON data to array.'
            );
        }

        /** @phpstan-ignore-next-line */
        $randIndex = array_rand(array: $data);

        if (!is_int(value: $randIndex)) {
            throw new TestException(
                message: 'Failed to resolve random data index.'
            );
        }

        if (!$data[$randIndex] instanceof stdClass) {
            throw new TestException(
                message: 'Random data index is not an anonymous object.'
            );
        }

        return $data[$randIndex];
    }
}
