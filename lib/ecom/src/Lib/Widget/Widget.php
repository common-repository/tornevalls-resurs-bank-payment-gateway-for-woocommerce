<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Widget;

use Resursbank\Ecom\Exception\FilesystemException;

/**
 * Basic widget functionality.
 */
class Widget
{
    /**
     * @throws FilesystemException
     */
    public function render(
        string $file
    ): string {
        ob_start();

        if (!file_exists(filename: $file)) {
            throw new FilesystemException(
                message: "Template file not found: $file"
            );
        }

        require $file;

        return (string) ob_get_clean();
    }
}
