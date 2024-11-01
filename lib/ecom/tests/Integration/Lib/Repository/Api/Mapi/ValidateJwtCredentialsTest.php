<?php

/**
* Copyright © Resurs Bank AB. All rights reserved.
* See LICENSE for license details.
*/

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Repository\Api\Mapi;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\ValidateJwtCredentials;

/**
* Test for JWT credentials validation.
*/
class ValidateJwtCredentialsTest extends TestCase
{
    /**
     * Assert exec() throws AuthException with invalid username.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testInvalidUsername(): void
    {
        Config::setup(
            jwtAuth: new Jwt(
                clientId: 'mamba',
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );

        $this->expectException(exception: AuthException::class);

        ValidateJwtCredentials::exec();
    }

    /**
     * Assert exec() throws AuthException with invalid password.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testInvalidPassword(): void
    {
        Config::setup(
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: 'core',
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );

        $this->expectException(exception: AuthException::class);

        ValidateJwtCredentials::exec();
    }

    /**
     * Assert exec() throws ConfigException without any credentials.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNoCredentials(): void
    {
        Config::setup();

        $this->expectException(exception: ConfigException::class);

        ValidateJwtCredentials::exec();
    }

    /**
     * Assert exec() return TRUE with valid credentials configured.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testValidCredentials(): void
    {
        Config::setup(
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );

        $this->assertTrue(condition: ValidateJwtCredentials::exec());
    }
}
