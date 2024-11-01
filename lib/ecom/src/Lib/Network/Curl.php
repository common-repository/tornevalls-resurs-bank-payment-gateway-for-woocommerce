<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network;

use CurlHandle;
use Exception;
use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt\Token;
use Resursbank\Ecom\Lib\Model\Network\Response;
use Resursbank\Ecom\Lib\Network\Curl\ErrorHandler;
use Resursbank\Ecom\Lib\Network\Curl\Header;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\GenerateToken;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use stdClass;

use function is_array;
use function is_string;

/**
 * Curl wrapper.
 *
 * @noinspection PhpClassHasTooManyDeclaredMembersInspection
 * @noinspection EfferentObjectCouplingInspection
 * @todo Check if ConfigException validation need testing in class methods.
 * @todo Refactor this class, its' too long (exceeds 250 lines). ECP-344
 */
// phpcs:ignore
class Curl
{
    /** @var CurlHandle */
    public readonly CurlHandle $ch;

    /** @var ContentType */
    public readonly ContentType $responseContentType;

    /**
     * @param array $headers
     * @param array $payload
     * @param bool $forceObject Enforces the JSON_FORCE_OBJECT flag on json_encode of payload
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @todo $headers and associated methods should be moved to a collection model / service layer.
     */
    public function __construct(
        string $url,
        public readonly RequestMethod $requestMethod,
        array $headers = [],
        array $payload = [],
        public readonly ContentType $contentType = ContentType::JSON,
        public readonly AuthType $authType = AuthType::JWT,
        public readonly ApiType $apiType = ApiType::MERCHANT,
        private readonly StringValidation $stringValidation = new StringValidation(),
        ?ContentType $responseContentType = null,
        private readonly bool $forceObject = false
    ) {
        $this->responseContentType = $responseContentType ?? $contentType;

        // Initialize Curl.
        $ch = $this->init(url: $url, headers: $headers, payload: $payload);

        // Setup plaintext auth if requested.
        $this->setAuth(ch: $ch);

        $this->ch = $ch;
    }

    /**
     * @param array $payload
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    public static function get(
        string $url,
        array $payload = [],
        AuthType $authType = AuthType::JWT
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::GET,
            payload: $payload,
            contentType: ContentType::URL,
            authType: $authType
        );

        return $curl->exec();
    }

    /**
     * @param array $payload
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    public static function post(
        string $url,
        array $payload = [],
        AuthType $authType = AuthType::JWT
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::POST,
            payload: $payload,
            authType: $authType
        );

        return $curl->exec();
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    public static function delete(
        string $url,
        AuthType $authType = AuthType::JWT
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::DELETE,
            authType: $authType
        );

        return $curl->exec();
    }

    /**
     * @param array $payload
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    public static function put(
        string $url,
        array $payload = [],
        AuthType $authType = AuthType::JWT,
        ContentType $contentType = ContentType::JSON,
        ?ContentType $responseContentType = null
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::PUT,
            payload: $payload,
            contentType: $contentType,
            authType: $authType,
            responseContentType: $responseContentType
        );

        return $curl->exec();
    }

    /**
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws IllegalValueException
     * @throws ConfigException
     * @todo Needs refactoring, method is too large. See ECP-345
     */
    // phpcs:ignore
    public function exec(): Response
    {
        /** @noinspection DuplicatedCode */
        $body = curl_exec(handle: $this->ch);

        // We want to check for errors immediately after running curl_exec
        $errorHandler = new ErrorHandler(
            body: $body,
            ch: $this->ch,
            contentType: $this->responseContentType
        );

        $errorHandler->validate();

        if (!is_string(value: $body)) {
            $exception = new IllegalTypeException(
                message: 'Curl response type is ' . gettype(
                    $body
                ) . ', expected string'
            );
            Config::getLogger()->error(message: $exception->getMessage());
            Config::getLogger()->error(message: $exception);
            throw $exception;
        }

        $code = (int)curl_getinfo(
            handle: $this->ch,
            option: CURLINFO_RESPONSE_CODE
        );

        if ($this->responseContentType === ContentType::JSON) {
            $this->stringValidation->notEmpty(value: $body);
            $body = json_decode(
                json: $body,
                associative: false,
                depth: 768,
                flags: JSON_THROW_ON_ERROR
            );
        } elseif ($this->responseContentType === ContentType::RAW) {
            $bodyObj = new stdClass();
            $bodyObj->message = $body;
            $body = $bodyObj;
        }

        if (!($body instanceof stdClass) && !is_array(value: $body)) {
            $exception = new IllegalTypeException(
                message: 'Curl response body is not an object or an array.'
            );
            Config::getLogger()->error(message: $exception->getMessage());
            Config::getLogger()->error(message: $exception);
            throw $exception;
        }

        curl_close(handle: $this->ch);

        return new Response(body: $body, code: $code);
    }

    /**
     * Fetch CURLINFO_EFFECTIVE_URL
     */
    public function getEffectiveUrl(): string
    {
        return (string) curl_getinfo(
            handle: $this->ch,
            option: CURLINFO_EFFECTIVE_URL
        );
    }

    public function hasBodyData(): bool
    {
        return
            $this->requestMethod === RequestMethod::POST ||
            $this->requestMethod === RequestMethod::PUT ||
            $this->requestMethod === RequestMethod::DELETE
        ;
    }

    /**
     * @param array $payload
     * @throws JsonException
     * @throws ValidationException
     * @throws ConfigException
     * @todo Add URL prefix based on $this->authType?
     */
    public function generateUrl(string $url, array $payload): string
    {
        $url .= $this->hasBodyData() || empty($payload)
            ? '' :
            '?' . $this->getPayloadData(payload: $payload);

        if (!filter_var(value: $url, filter: FILTER_VALIDATE_URL)) {
            $exception = new ValidationException(
                message: 'Invalid URL requested (' . $url . ').'
            );
            Config::getLogger()->error(message: $exception->getMessage());
            Config::getLogger()->error(message: $exception);
            throw $exception;
        }

        return $url;
    }

    /**
     * @param array $payload
     * @throws JsonException
     * @todo Consider caching this is a local variable on this instance to avoid subsequent calls. NOTE: Generating this
     * @todo data directly in the constructor harms refactoring.
     */
    public function getPayloadData(
        array $payload
    ): string {
        $flags = JSON_THROW_ON_ERROR;

        if ($this->forceObject) {
            $flags = JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT;
        }

        return match ($this->contentType) {
            ContentType::EMPTY, ContentType::RAW => '',
            ContentType::JSON => json_encode(
                value: $payload,
                flags: JSON_THROW_ON_ERROR | $flags
            ),
            ContentType::URL => http_build_query(data: $payload)
        };
    }

    /**
     * @param array $headers
     * @param array $payload
     * @throws JsonException
     * @throws ValidationException
     * @throws Exception
     * @todo Check if CURLOPT_ENCODING should be included and what value it should be assigned.
     */
    private function init(
        string $url,
        array $headers,
        array $payload
    ): CurlHandle {
        /** @noinspection DuplicatedCode */
        $ch = curl_init();

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            // Don't treat HTTP code 400+ as error.
            CURLOPT_FAILONERROR => false,
            // Follow redirects.
            CURLOPT_AUTOREFERER => true,
            // Track outgoing headers for debugging.
            CURLINFO_HEADER_OUT => true,
            // Do not include header in output.
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => Header::getUserAgent(),
            CURLOPT_HTTPHEADER => Header::getHeadersData(
                headers: Header::generateHeaders(
                    headers: $headers,
                    payloadData: $this->getPayloadData(payload: $payload),
                    contentType: $this->contentType,
                    hasBodyData: $this->hasBodyData()
                )
            ),
            CURLOPT_CUSTOMREQUEST => $this->getCustomRequestValue(),
            CURLOPT_URL => $this->generateUrl(url: $url, payload: $payload),
            CURLOPT_SSLVERSION => CURL_SSLVERSION_DEFAULT,
        ];

        if (!empty(Config::getProxy())) {
            $options[CURLOPT_PROXY] = Config::getProxy();
            $options[CURLOPT_PROXYTYPE] = Config::getProxyType();
        }

        if (Config::getTimeout()) {
            $options[CURLOPT_CONNECTTIMEOUT] = ceil(
                num: Config::getTimeout()
            ) / 2;
            $options[CURLOPT_TIMEOUT] = ceil(num: Config::getTimeout());
        }

        curl_setopt_array(handle: $ch, options: $options);

        $this->setContent(ch: $ch, payload: $payload);

        return $ch;
    }

    private function getCustomRequestValue(): string
    {
        return match ($this->requestMethod) {
            RequestMethod::GET => 'GET',
            RequestMethod::POST => 'POST',
            RequestMethod::PUT => 'PUT',
            RequestMethod::DELETE => 'DELETE'
        };
    }

    /**
     * Append POST | PUT data / options to CURL.
     *
     * @param array $payload
     * @throws JsonException
     */
    private function setContent(CurlHandle $ch, array $payload): void
    {
        if ($this->contentType === ContentType::EMPTY) {
            return;
        }

        $data = $this->getPayloadData(payload: $payload);

        if ($data !== '' && $this->hasBodyData()) {
            curl_setopt(handle: $ch, option: CURLOPT_POSTFIELDS, value: $data);
        }

        if ($this->requestMethod !== RequestMethod::POST) {
            return;
        }

        curl_setopt(handle: $ch, option: CURLOPT_POST, value: true);
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    private function setAuth(CurlHandle $ch): void
    {
        switch ($this->authType) {
            case AuthType::BASIC:
                $this->setBasicAuth(ch: $ch);
                break;

            case AuthType::JWT:
                $this->setJwtAuth(ch: $ch);
                break;

            case AuthType::NONE:
                break;
        }
    }

    /**
     * @throws ConfigException
     */
    private function setBasicAuth(CurlHandle $ch): void
    {
        $auth = Config::getBasicAuth();

        if ($auth === null) {
            $exception = new ConfigException(
                message: 'Basic auth is not configured.'
            );
            Config::getLogger()->error(message: $exception->getMessage());
            Config::getLogger()->error(message: $exception);
            throw $exception;
        }

        curl_setopt(
            handle: $ch,
            option: CURLOPT_USERPWD,
            value: "$auth->username:$auth->password"
        );
    }

    /**
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     * @throws ReflectionException
     * @throws ApiException
     * @throws ConfigException
     */
    private function setJwtAuth(CurlHandle $ch): void
    {
        $auth = Config::getJwtAuth();

        if ($auth === null) {
            $exception = new ConfigException(
                message: 'JWT auth is not configured.'
            );
            Config::getLogger()->error(message: $exception->getMessage());
            Config::getLogger()->error(message: $exception);
            throw $exception;
        }

        curl_setopt(
            handle: $ch,
            option: CURLOPT_HTTPAUTH,
            value: CURLAUTH_BEARER
        );

        curl_setopt(
            handle: $ch,
            option: CURLOPT_XOAUTH2_BEARER,
            value: $this->getJwtToken(auth: $auth)->access_token
        );
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    private function getJwtToken(
        Jwt $auth
    ): Token {
        $result = $auth->getToken();

        if ($result === null || $result->isExpired()) {
            $result = (new GenerateToken(auth: $auth))->call();
        }

        return $result;
    }
}
