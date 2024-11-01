<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Woocommerce\Database\Options\OrderManagement;

use Resursbank\Woocommerce\Database\DataType\BoolOption;
use Resursbank\Woocommerce\Database\OptionInterface;

/**
 * Implementation of resursbank_enable_capture value in options table.
 */
class EnableCapture extends BoolOption implements OptionInterface
{
    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return self::NAME_PREFIX . 'enable_capture';
    }

    public static function getDefault(): ?string
    {
        return 'yes';
    }
}
