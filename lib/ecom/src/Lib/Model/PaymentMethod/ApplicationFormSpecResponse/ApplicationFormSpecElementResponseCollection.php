<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Collection of ApplicationFormSpecElementResponse objects.
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class ApplicationFormSpecElementResponseCollection extends Collection
{
    /**
     * @param array $data
     * @throws IllegalTypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(
            data: $data,
            type: ApplicationFormSpecElementResponse::class
        );
    }
}
