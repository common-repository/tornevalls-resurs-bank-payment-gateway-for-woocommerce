<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use Exception;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;

use function chr;
use function ord;
use function strlen;

/**
 * Class for string manipulation.
 */
class Strings
{
    /**
     * Obfuscate strings between first and last character, just like RCO.
     */
    public static function getObfuscatedString(string $string, int $startAt = 1, int $endAt = 1): string
    {
        $stringLength = strlen(string: $string);
        return $stringLength > $startAt - 1 ?
            substr(string: $string, offset: 0, length: $startAt) .
            str_repeat(string: '*', times: $stringLength - 2) .
            substr(
                string: $string,
                offset: $stringLength - $endAt,
                length: $stringLength - $endAt
            ) : $string;
    }

    /**
     * Base64-encoded data, but with URL-safe characters.
     */
    public static function base64urlEncode(string $data): string
    {
        return rtrim(
            string: strtr(base64_encode($data), '+/', '-_'),
            characters: '='
        );
    }

    /**
     * Base64-decoded data, but with URL-safe characters.
     */
    public static function base64urlDecode(string $data): string
    {
        return (string)base64_decode(
            string: str_pad(
                string: strtr($data, '-_', '+/'),
                length: strlen($data) % 4,
                pad_string: '='
            ),
            strict: false
        );
    }

    /**
     * Generate a random UUID.
     *
     * @throws Exception
     * @throws IllegalValueException
     */
    public static function getUuid(): string
    {
        $data = random_bytes(length: 16);

        if (strlen(string: $data) !== 16) {
            throw new IllegalValueException(message: 'Missing random bytes.');
        }

        $data[6] = chr(codepoint: ord(character: $data[6]) & 0x0f | 0x40);
        $data[8] = chr(codepoint: ord(character: $data[8]) & 0x3f | 0x80);

        return vsprintf(
            format: '%s%s-%s-%s-%s-%s%s%s',
            values: str_split(string: bin2hex(string: $data), length: 4)
        );
    }
}
