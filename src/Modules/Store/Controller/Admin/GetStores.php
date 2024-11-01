<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Woocommerce\Modules\Store\Controller\Admin;

use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Api\Environment as EnvironmentEnum;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Store\Http\GetStoresController;
use Resursbank\Ecom\Module\Store\Repository;
use Resursbank\Woocommerce\Modules\Api\Connection;
use Resursbank\Woocommerce\Util\Log;
use Resursbank\Woocommerce\Util\Translator;
use Throwable;

/**
 * Resolve JSON encoded list of available stores based on submitted credentials.
 */
class GetStores extends GetStoresController
{
    /**
     * @throws HttpException
     */
    public function exec(): string
    {
        $result = '';

        try {
            $data = $this->getRequestData();

            Connection::setup(
                jwt: new Jwt(
                    clientId: $data->clientId,
                    clientSecret: $data->clientSecret,
                    scope: $data->environment === EnvironmentEnum::PROD ?
                        Scope::MERCHANT_API :
                        Scope::MOCK_MERCHANT_API,
                    grantType: GrantType::CREDENTIALS
                )
            );

            $result = json_encode(
                value: Repository::getApi()->getSelectList(),
                flags: JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT
            );
        } catch (AuthException) {
            $result = '{"error": "' .
                Translator::translate(
                    phraseId: 'api-connection-failed-bad-credentials'
                ) .
                '"}';
        } catch (Throwable $error) {
            Log::error(error: $error);

            throw new HttpException(
                message: Translator::translate(
                    phraseId: 'get-stores-could-not-fetch'
                ) . ' Error: ' . $error->getMessage()
            );
        }

        return $result;
    }
}
