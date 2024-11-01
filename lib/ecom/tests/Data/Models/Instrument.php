<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Models;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Mock model.
 */
class Instrument extends Model
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {
    }
}
