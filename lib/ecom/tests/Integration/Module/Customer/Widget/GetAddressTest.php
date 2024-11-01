<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Customer\Widget;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Module\Customer\Widget\GetAddress;

/**
 * Integration tests for the GetAddress widget.
 */
class GetAddressTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
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

        parent::setUp();
    }

    /**
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function testRenderPaymentMethods(): void
    {
        $data = new GetAddress(
            govId: '',
            customerType: CustomerType::NATURAL,
            fetchUrl: ''
        );

        static::assertStringContainsString(
            needle: Translator::translate(
                phraseId: 'get-address-could-not-fetch-address'
            ),
            haystack: $data->content,
            message: 'Could not fetch an address for the given ID. Please ' .
                'update the ID or refresh the page and try again.'
        );

        static::assertStringContainsString(
            needle: Translator::translate(
                phraseId: 'get-address-no-callback-function'
            ),
            haystack: $data->content,
            message: 'The address was fetched, but could not be handled ' .
                'properly. Please contact the store owner if the problem persists.'
        );

        static::assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-customer-widget-getAddress/s',
            string: $data->content,
            message: 'Get address widget should contain a div with class ' .
                'rb-customer-widget-getAddress.'
        );

        static::assertMatchesRegularExpression(
            pattern: '/<input[^>]+id=["\'][^"\']*rb-customer-widget-getAddress-customerType-natural/s',
            string: $data->content,
            message: 'Get address widget should contain an input with id ' .
                'rb-customer-widget-getAddress-customerType-natural.'
        );

        static::assertMatchesRegularExpression(
            pattern: '/<input[^>]+id=["\'][^"\']*rb-customer-widget-getAddress-customerType-legal/s',
            string: $data->content,
            message: 'Get address widget should contain an input with id ' .
            'rb-customer-widget-getAddress-customerType-legal.'
        );
    }
}
