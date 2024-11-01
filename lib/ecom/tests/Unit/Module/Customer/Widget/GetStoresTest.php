<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Customer\Widget;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Store\Widget\GetStores;

/**
 * Tests for the GetStores widget.
 */
class GetStoresTest extends TestCase
{
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(path: '/tmp/ecom-test/customer/' . time()),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );
    }

    /**
     * Test that supplied variables are set in the rendered widget.
     *
     * @throws \Resursbank\Ecom\Exception\FilesystemException
     */
    public function testRenderedContent(): void
    {
        $fetchUrl = 'https://example.com/foo';
        $environmentSelectId = 'environment_select';
        $clientIdInputId = 'client_id_input';
        $clientSecretInputId = 'client_secret_input';
        $storeSelectId = 'store_select';
        $spinnerClass = 'spinner_class';

        $widget = new GetStores(
            fetchUrl: $fetchUrl,
            environmentSelectId: $environmentSelectId,
            clientIdInputId: $clientIdInputId,
            clientSecretInputId: $clientSecretInputId,
            storeSelectId: $storeSelectId,
            spinnerClass: $spinnerClass
        );

        $this->assertStringContainsString(
            needle: "document.getElementById('" . $storeSelectId . "')",
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: "document.getElementById('" . $environmentSelectId . "')",
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: "document.getElementById('" . $clientIdInputId . "')",
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: "document.getElementById('" . $clientSecretInputId . "')",
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: "storeSelect.parentElement.classList.add('" .  $spinnerClass . "');",
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: "fetch('" . $fetchUrl . "', {",
            haystack: $widget->content
        );
    }
}
