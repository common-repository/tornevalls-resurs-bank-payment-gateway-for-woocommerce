<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethod\Widget;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod\LegalLink;
use Resursbank\Ecom\Lib\Order\PaymentMethod\LegalLink\Type;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Widget\PartPayment;

/**
 * Integration test for the Part payment widget
 */
class PartPaymentTest extends TestCase
{
    /**
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
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );
    }

    /**
     * Verify that Part payment widget appears to contain correct data
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testRenderPartPayment(): void
    {
        $paymentMethod = Repository::getById(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
        );

        if ($paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $expectedUrl = '';

        /** @var LegalLink $legalLink */
        foreach ($paymentMethod->legalLinks as $legalLink) {
            if ($legalLink->type !== Type::PRICE_INFO) {
                continue;
            }

            $expectedUrl = $legalLink->url;
        }

        $widget = new PartPayment(
            storeId: $_ENV['STORE_ID'],
            paymentMethod: $paymentMethod,
            months: 3,
            amount: 1200,
            currencyFormat: CurrencyFormat::SYMBOL_LAST,
            currencySymbol: 'kr',
            apiUrl: 'https://example.com'
        );

        $this->assertStringContainsString(
            needle: Translator::translate(phraseId: 'read-more'),
            haystack: $widget->content,
            message: 'Read more link not found.'
        );
        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-pp/s',
            string: $widget->content,
            message: 'Widget should contain a div with class rb-pp.'
        );
        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+id=["\'][^"\']*rb-pp-iframe-container/s',
            string: $widget->content,
            message: 'Widget should contain a div with id rb-pp-iframe-container'
        );
        $testUrl = str_replace(
            search: ['/', '?', '&', '-', '.'],
            replace: ['\\/', '\\?', '\\&', '\\-', '\\.'],
            subject: $expectedUrl
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<iframe[^>]+src=[\"']$testUrl/s",
            string: $widget->content,
            message: 'Read more widget should contain an iframe with the correct URL.'
        );
    }

    /**
     * Verify that the part payment widget contains the starting at value returned by getStartingAtCost
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ValidationException
     */
    public function testGetStartingAtCost(): void
    {
        $paymentMethod = Repository::getById(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
        );

        if ($paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $widget = new PartPayment(
            storeId: $_ENV['STORE_ID'],
            paymentMethod: $paymentMethod,
            months: 3,
            amount: 1200,
            currencyFormat: CurrencyFormat::SYMBOL_LAST,
            currencySymbol: 'kr',
            apiUrl: 'https://example.com'
        );
        $startingAt = $widget->getStartingAtCost();

        $this->assertStringContainsString(
            needle: $startingAt,
            haystack: $widget->content,
            message: 'Widget should contain starting at cost'
        );
    }
}
