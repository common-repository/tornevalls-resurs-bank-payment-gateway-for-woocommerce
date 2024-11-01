<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store\Models;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Module\Store\Repository;

/**
 * Defines a Store collection.
 */
class StoreCollection extends Collection
{
    /**
     * @param array $data
     * @throws IllegalTypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: Store::class);
    }

    /**
     * Convert collection data to assoc array prepared for select elements.
     */
    public function getSelectList(): array
    {
        $result = [];

        /** @var Store $store */
        foreach ($this->getData() as $store) {
            $result[$store->id] = "$store->nationalStoreId: $store->name";
        }

        return $result;
    }

    /**
     * Resolve ID value of only available store.
     *
     * @return string|null
     */
    public function getSingleStoreId(): ?string
    {
        return count($this->getData()) === 1 ?
            $this->offsetGet(offset: 0)->id : null;
    }
}
