<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Woocommerce\Database;

use function is_string;

/**
 * Basic database interface for options in options table.
 */
abstract class Option
{
    /**
     * Name prefix for entries in options table.
     */
    public const NAME_PREFIX = 'resursbank_';

    /**
     * Resolve data from options table as string, defaults to NULL.
     *
     * @noinspection PhpArgumentWithoutNamedIdentifierInspection
     */
    public static function getRawData(): ?string
    {
        $val = get_option(
            static::getName(),
            null
        );

        return is_string(value: $val) ? $val : null;
    }

    /**
     * Sets option data.
     *
     * @noinspection PhpArgumentWithoutNamedIdentifierInspection
     */
    public static function setData(string $value): bool
    {
        return update_option(
            static::getName(),
            $value
        ) === true;
    }
}
