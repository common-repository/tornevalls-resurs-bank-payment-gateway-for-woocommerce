<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\SupportInfo\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Widget\Widget;
use stdClass;
use Throwable;

/**
 * Support info widget which displays basic information about the state of the library.
 */
class SupportInfo extends Widget
{
    private readonly string $html;

    /**
     * @param string $pluginVersion Version of the calling plugin/addon
     * @throws FilesystemException
     */
    public function __construct(
        public readonly string $pluginVersion = ''
    ) {
        $this->html = $this->render(file: __DIR__ . '/support-info.phtml');
    }

    /**
     * Return the widget HTML.
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * Fetches the current PHP version.
     */
    public function getPhpVersion(): string
    {
        return PHP_VERSION;
    }

    /**
     * Fetches the current OpenSSL version.
     */
    public function getSslVersion(): string
    {
        if (defined(constant_name: 'OPENSSL_VERSION_TEXT')) {
            return OPENSSL_VERSION_TEXT;
        }

        return '';
    }

    /**
     * Fetches the current Curl version.
     */
    public function getCurlVersion(): string
    {
        return curl_version()['version'];
    }

    /**
     *  Attempt to fetch the current version of Ecom from the composer.json file.
     *
     * @throws ConfigException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws IllegalTypeException
     */
    public function getEcomVersion(): string
    {
        try {
            $composerJson = file_get_contents(
                filename: __DIR__ . '/../../../../composer.json'
            );

            if (!$composerJson) {
                throw new EmptyValueException(
                    message: 'Unable to load contents of composer.json'
                );
            }

            $decoded = json_decode(
                json: $composerJson,
                associative: null,
                depth: 256,
                flags: JSON_THROW_ON_ERROR
            );

            if (!$decoded instanceof stdClass) {
                throw new IllegalTypeException(
                    message: 'Decoded JSON data not of type stdClass'
                );
            }

            return $decoded->version;
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }

        return '';
    }
}
