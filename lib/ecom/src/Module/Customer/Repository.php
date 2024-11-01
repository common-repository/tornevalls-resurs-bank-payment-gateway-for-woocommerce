<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Customer;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\GetAddressException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Lib\Utilities\Session;
use Resursbank\Ecom\Module\Customer\Api\GetAddress;
use Resursbank\Ecom\Module\Customer\Models\GetAddressRequest;
use stdClass;
use Throwable;

/**
 * Customer repository.
 */
class Repository
{
    use ExceptionLog;

    /**
     * Session key (without prefix) for government id.
     */
    public const SESSION_KEY_SSN_DATA = 'ssn_data';

    /**
     * Session key (without prefix) for customer type.
     */
    public const SESSION_KEY_CUSTOMER_TYPE = 'customer_type';

    /**
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws GetAddressException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @throws ConfigException
     */
    public static function getAddress(
        string $storeId,
        string $governmentId,
        CustomerType $customerType,
        GetAddress $api = new GetAddress()
    ): Address {
        try {
            return $api->call(
                storeId: $storeId,
                governmentId: $governmentId,
                customerType: $customerType
            );
        } catch (Throwable $e) {
            self::logException(exception: $e);

            throw $e;
        }
    }

    /**
     * Store SSN data in session.
     *
     * NOTE: $sessionHandler to support testing with mocked session handler.
     *
     * @throws ConfigException
     */
    public static function setSsnData(
        GetAddressRequest $data,
        Session $sessionHandler = new Session()
    ): void {
        try {
            $sessionHandler->set(
                key: self::SESSION_KEY_SSN_DATA,
                val: json_encode(value: $data, flags: JSON_THROW_ON_ERROR)
            );
        } catch (Throwable $e) {
            self::logException(exception: $e);
            // Failing is harmless, client can supply info on gateway.
        }
    }

    /**
     * Get SSN data from PHP session.
     *
     * NOTE: $sessionHandler to support testing with mocked session handler.
     *
     * @throws ConfigException
     */
    public static function getSsnData(
        Session $sessionHandler = new Session()
    ): ?GetAddressRequest {
        $result = null;

        try {
            $data = $sessionHandler->get(key: self::SESSION_KEY_SSN_DATA);

            if ($data !== '') {
                $data = json_decode(
                    json: $data,
                    associative: false,
                    depth: 512,
                    flags: JSON_THROW_ON_ERROR
                );
            }

            if ($data instanceof stdClass) {
                $result = DataConverter::stdClassToType(
                    object: $data,
                    type: GetAddressRequest::class
                );

                if (!$result instanceof GetAddressRequest) {
                    throw new IllegalValueException(
                        message: 'Session data is not SSN data.'
                    );
                }
            }
        } catch (Throwable $e) {
            $result = null;
            self::logException(exception: $e);
            // Failing is harmless, client can supply info on gateway.
        }

        return $result;
    }
}
