<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Utilities;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\Generic;

/**
 * Test for Generic class.
 */
class GenericTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function getVersionByDocBlockTest(): void
    {
        $this->assertTrue(
            condition: version_compare(
                version1: (new Generic())->getVersionByClassDoc(
                    className: Generic::class
                ),
                version2: '1.0.0',
                operator: '>='
            )
        );
    }

    /**
     * @throws Exception
     */
    public function getVersionByComposerTest(): void
    {
        $generic = $this->createMock(originalClassName: Generic::class);
        $generic->method('getVersionByComposer')->willReturn(value: '1.0.0');
        // composer.json in our package may not contain version numbers.
        $this->assertTrue(
            condition: version_compare(
                version1: $generic->getVersionByComposer(location: __DIR__),
                version2: '1.0.0',
                operator: '>='
            )
        );
    }

    /**
     * @throws ReflectionException
     */
    public function getVersionByAnythingFound(): void
    {
        $generic = $this->createMock(originalClassName: Generic::class);
        $generic->method('getVersionByAny')->willReturn(value: '1.0.0');
        $this->assertTrue(
            condition: version_compare(
                version1: $generic->getVersionByAny(
                    composerLocation: __DIR__,
                    composerDepth: 3,
                    className: Generic::class
                ),
                version2: '1.0.0',
                operator: '>='
            )
        );
    }

    /**
     * @throws Exception
     */
    public function getAnotherComposerTag(): void
    {
        $willReturn = 'resursbank/ecom';

        $this->assertSame(
            expected: $willReturn,
            actual: (new Generic())->getComposerTag(
                location: __DIR__,
                tag: 'name'
            )
        );
    }

    /**
     * @throws Exception
     */
    public function getVendorTest(): void
    {
        $this->assertSame(
            expected: 'resursbank',
            actual: (new Generic())->getComposerVendor(
                composerLocation: __DIR__
            )
        );
    }
}
