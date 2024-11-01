<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\DataConverter;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * To test the conversion to collection from array
 */
class SimpleDummyCollection extends Collection
{
    /**
     * @param array $data
     * @throws IllegalTypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: SimpleDummy::class);
    }
}
