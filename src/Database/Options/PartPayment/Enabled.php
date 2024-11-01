<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Woocommerce\Database\Options\PartPayment;

use Resursbank\Woocommerce\Database\DataType\BoolOption;
use Resursbank\Woocommerce\Database\OptionInterface;

/**
 * Implementation of resursbank_part_payment_enabled value in options table.
 */
class Enabled extends BoolOption implements OptionInterface
{
    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return self::NAME_PREFIX . 'part_payment_enabled';
    }
}
