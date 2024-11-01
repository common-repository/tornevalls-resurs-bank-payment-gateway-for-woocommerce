<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Callback\Http;

use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Action;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Callback\Http\ManagementController as Controller;
use Resursbank\EcomTest\Data\Models\Instrument;

/**
 * Tests for management callbacks.
 *
 * @todo Improve test coverage.
 */
class ManagementControllerTest extends TestCase
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
            cache: $this->createMock(originalClassName: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );
    }

    /**
     * Create a mocked version of the Controller class, setting the return value
     * of the getInputData method, in an effort to replicate behaviour with
     * incoming input data to PHP (faking the contents of php://input).
     *
     * @param array<string, string> $data
     * @throws JsonException
     */
    private function getControllerWithMockedInputData(array $data): Controller
    {
        $controller = $this->createPartialMock(
            originalClassName: Controller::class,
            methods: ['getInputData']
        );

        /** @noinspection PhpArgumentWithoutNamedIdentifierInspection */
        $controller->expects($this->once())
            ->method(constraint: 'getInputData')
            ->willReturn(
                value: json_encode(value: $data, flags: JSON_THROW_ON_ERROR)
            );

        return $controller;
    }

    /**
     * Assert that getRequestData() throws HttpException with code 415 when
     * supplied that does not convert to a Management instance.
     *
     * @throws HttpException
     * @throws JsonException
     */
    public function testGetRequestDataThrowsWithInaccurateData(): void
    {
        $controller = $this->getControllerWithMockedInputData(
            data: ['wherever' => 'whenever']
        );

        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 415);

        $controller->getRequestData();
    }

    /**
     * Assert that getRequestData() throws HttpException with code 415 when
     * getRequestModel() returns an unexpected instance of Model.
     *
     * @throws HttpException
     */
    public function testGetRequestDataThrowsWithInvalidConversion(): void
    {
        $controller = $this->createPartialMock(
            originalClassName: Controller::class,
            methods: ['getRequestModel']
        );

        /** @noinspection PhpArgumentWithoutNamedIdentifierInspection */
        $controller->expects($this->once())
            ->method(constraint: 'getRequestModel')
            ->willReturn(value: new Instrument(id: 5, name: 'Bass'));

        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 415);

        $controller->getRequestData();
    }

    /**
     * Assert that getRequestData() returns input data unaffected in forms of
     * Model instance.
     *
     * @throws HttpException
     * @throws JsonException
     */
    public function testGetRequestData(): void
    {
        $paymentId = '78a85554-1297-4862-b3fc-44c63f81afa9';
        $actionId = '88a89954-cb9f-4462-bcf1-12c33f71a91c';
        $controller = $this->getControllerWithMockedInputData(
            data: [
                'paymentId' => $paymentId,
                'action' => Action::CAPTURE->value,
                'actionId' => $actionId,
                'created' => '2020-12-20 05:40:21',
            ]
        );

        $data = $controller->getRequestData();

        $this->assertSame(expected: Action::CAPTURE, actual: $data->action);
        $this->assertSame(expected: $paymentId, actual: $data->paymentId);
        $this->assertSame(expected: $actionId, actual: $data->actionId);
    }
}
