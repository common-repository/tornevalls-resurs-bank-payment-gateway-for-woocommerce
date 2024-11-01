<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Api;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Throwable;

use function strlen;

/**
 * Tests for the Resursbank\Ecom\Lib\Api\Mapi class.
 */
class MapiTest extends TestCase
{
    private Mapi $mapi;

    protected function setUp(): void
    {
        $this->mapi = new Mapi();

        $this->setupConfig();

        parent::setUp();
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function setupConfig(
        bool $prod = false
    ): void {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            isProduction: $prod
        );
    }

    /**
     * Resolve a random route name.
     */
    private function getRoute(): string
    {
        $route = '';

        try {
            $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $length = random_int(min: 1, max: 50);

            for ($i = 0; $i < $length; $i++) {
                $route .= $charset[random_int(
                    min: 0,
                    max: strlen(string: $charset) - 1
                )];
            }
        } catch (Throwable) {
            $this->fail(message: 'Failed to generate route.');
        }

        return $route;
    }

    private function getExpectedUrl(
        string $route = '',
        string $host = Mapi::URL_TEST
    ): string {
        return "$host$route";
    }

    /**
     * Assert getUrl() throws EmptyValueException without $route value.
     *
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws ValidationException
     */
    public function testGetUrlThrowsWithEmptyRoute(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->mapi->getUrl(route: '');
    }

    /**
     * Assert getUrl() returns URL to test endpoint.
     *
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws ValidationException
     */
    public function testGetUrlReturnsTestUrl(): void
    {
        $route = $this->getRoute();

        $this->assertSame(
            expected: $this->getExpectedUrl(route: $route),
            actual: $this->mapi->getUrl(route: $route)
        );
    }

    /**
     * Assert getUrl() returns URL to production endpoint.
     *
     * @throws EmptyValueException
     * @throws ValidationException
     * @throws ConfigException
     */
    public function testGetUrlReturnsProdUrl(): void
    {
        $this->setupConfig(prod: true);

        $route = $this->getRoute();

        $this->assertSame(
            expected: $this->getExpectedUrl(
                route: $route,
                host: Mapi::URL_PROD
            ),
            actual: $this->mapi->getUrl(route: $route)
        );
    }
}
