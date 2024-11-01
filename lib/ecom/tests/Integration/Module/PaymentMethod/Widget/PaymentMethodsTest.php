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
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Widget\PaymentMethods;

use function count;
use function number_format;

/**
 * Integration tests for the PaymentMethods widget.
 */
class PaymentMethodsTest extends TestCase
{
    private PaymentMethodCollection $methods;

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(
                path: '/tmp/ecom-test/paymentMethods/' . time()
            ),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );

        $this->methods = Repository::getPaymentMethods(
            storeId: $_ENV['STORE_ID']
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
        if ((bool) $_ENV['IS_PIPELINE']) {
            $this->markTestSkipped(
                message: 'Buffer does not work in pipeline, skipping.'
            );
        }

        $this->assertTrue(condition: count($this->methods) > 0);

        $data = new PaymentMethods(paymentMethods: $this->methods);

        $this->assertStringContainsString(
            needle: Translator::translate(phraseId: 'name'),
            haystack: $data->content,
            message: 'Name table header not found.'
        );

        $this->assertStringContainsString(
            needle: Translator::translate(phraseId: 'min-total'),
            haystack: $data->content,
            message: 'Minimum total table header not found.'
        );

        $this->assertStringContainsString(
            needle: Translator::translate(phraseId: 'max-total'),
            haystack: $data->content,
            message: 'Maximum total table header not found.'
        );

        $this->assertStringContainsString(
            needle: Translator::translate(phraseId: 'sort-order'),
            haystack: $data->content,
            message: 'Sort order table header not found.'
        );

        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-payment-methods/s',
            string: $data->content,
            message: 'Payment methods widget should contain a div with class rb-payment-methods.'
        );

        /** @var PaymentMethod $method */
        foreach ($this->methods as $method) {
            $this->assertMatchesRegularExpression(
                pattern: '/<tr[^>]*id=["\']rb-pm-' . $method->id . '["\']>/s',
                string: $data->content,
                message: "Missing row matching payment method $method->id"
            );

            $this->assertMatchesRegularExpression(
                pattern: '/<tr[^>]*id=["\']rb-pm-' . $method->id . '["\'][^>]*>.*<td.*>[^<]*' .
                    preg_quote(
                        str: $method->name,
                        delimiter: '/'
                    ) . '.*<\/td>/s',
                string: $data->content,
                message: "Missing name column for payment method row matching $method->id"
            );

            $this->assertMatchesRegularExpression(
                pattern: '/<tr[^>]*id=["\']rb-pm-' . $method->id . '["\'][^>]*>.*<td.*>[^<]*' .
                    number_format(
                        num: $method->minPurchaseLimit,
                        decimals: 2
                    ) . '.*<\/td>/s',
                string: $data->content,
                message: "Missing min purchase limit column for payment method row matching $method->id"
            );

            $this->assertMatchesRegularExpression(
                pattern: '/<tr[^>]*id=["\']rb-pm-' . $method->id . '["\'][^>]*>.*<td.*>[^<]*' .
                    number_format(
                        num: $method->maxPurchaseLimit,
                        decimals: 2
                    ) . '.*<\/td>/s',
                string: $data->content,
                message: "Missing max purchase limit column for payment method row matching $method->id"
            );

            $this->assertMatchesRegularExpression(
                pattern: '/<tr[^>]*id=["\']rb-pm-' . $method->id . '["\'][^>]*>.*<td.*>[^<]*' .
                    $method->sortOrder . '.*<\/td>/s',
                string: $data->content,
                message: "Missing sort order column for payment method row matching $method->id"
            );
        }
    }

    /**
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ConfigException
     */
    public function testRenderPaymentMethodsWarning(): void
    {
        if ((bool) $_ENV['IS_PIPELINE']) {
            $this->markTestSkipped(
                message: 'Buffer does not work in pipeline, skipping.'
            );
        }

        $data = new PaymentMethods(
            paymentMethods: new PaymentMethodCollection(data: [])
        );

        $this->assertStringContainsString(
            needle: Translator::translate(phraseId: 'no-payment-methods'),
            haystack: $data->content,
            message: 'No payment methods warning not found.'
        );
    }
}
