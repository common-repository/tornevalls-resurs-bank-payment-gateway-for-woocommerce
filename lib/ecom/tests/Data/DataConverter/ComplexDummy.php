<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\DataConverter;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * To test stdClass conversion of objects specifying object properties.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ComplexDummy extends Model
{
    public function __construct(
        public int $int,
        public SimpleDummy $simpleDummy,
        public SimpleDummyCollection $simpleDummyCollection
    ) {
    }
}
