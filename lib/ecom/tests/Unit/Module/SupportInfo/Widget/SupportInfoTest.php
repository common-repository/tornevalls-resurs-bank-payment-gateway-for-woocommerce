<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\SupportInfo\Widget;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\SupportInfo\Widget\SupportInfo;

/**
 * Unit tests for the Support Info widget class.
 */
class SupportInfoTest extends TestCase
{
    /**
     * Initialize the environment.
     *
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new None(),
            language: Language::sv,
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );
    }

    /**
     * Assert that the widget appears to render as intended.
     *
     * @throws FilesystemException
     */
    public function testRenderWidget(): void
    {
        $pluginVersion = '1.3.3.7';
        $widget = new SupportInfo(pluginVersion: $pluginVersion);

        $this->assertStringContainsString(
            needle: '<td>' . $pluginVersion . '</td>',
            haystack: $widget->getHtml(),
            message: 'Support Info widget is missing the plugin version'
        );
        $this->assertStringContainsString(
            needle: '<td>' . PHP_VERSION . '</td>',
            haystack: $widget->getHtml(),
            message: 'Support Info widget is missing the PHP version'
        );
    }
}
