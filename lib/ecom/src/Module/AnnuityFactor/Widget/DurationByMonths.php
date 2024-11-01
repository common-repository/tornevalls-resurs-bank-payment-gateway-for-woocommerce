<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\AnnuityFactor\Widget;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Widget\Widget;

use function str_contains;

/**
 * Generates script intended to fetch duration in months options for PartPayment widget configuration
 */
class DurationByMonths extends Widget
{
    /** @var string */
    public readonly string $separator;

    /** @var string */
    private readonly string $generatedScript;

    /**
     * @throws FilesystemException
     */
    public function __construct(
        public readonly string $endpointUrl
    ) {
        $this->separator = str_contains(
            haystack: $this->endpointUrl,
            needle: '?'
        ) ? '&' : '?';

        $this->generatedScript = $this->render(
            file: __DIR__ . '/DurationByMonths.phtml'
        );
    }

    public function getScript(): string
    {
        return $this->generatedScript;
    }
}
