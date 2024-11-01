<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PriceSignage\Models;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines price signage entity.
 */
class PriceSignage extends Model
{
    /**
     * @todo These are all specified as required properties, but it does not state whether they can be empty?
     */
    public function __construct(
        public readonly UriLinkCollection $secciLinks,
        public readonly UriLinkCollection $generalTermsLinks,
        public readonly CostCollection $costList
    ) {
    }
}
