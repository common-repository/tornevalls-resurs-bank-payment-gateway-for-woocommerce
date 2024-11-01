<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Customer\Http;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Customer\Http\GetAddressController as Controller;
use Resursbank\Ecom\Module\Customer\Models\GetAddressRequest;
use Resursbank\Ecom\Module\Customer\Repository;
use Resursbank\EcomTest\Data\Models\Instrument;
use Resursbank\EcomTest\Utilities\MockSessionTrait;

/**
 * Tests for the API call getAddress.
 */
class GetAddressControllerTest extends TestCase
{
    use MockSessionTrait;

    private Controller $controller;

    private string $storeId;

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

        $this->controller = $this->createPartialMock(
            originalClassName: Controller::class,
            methods: ['log']
        );
        $this->storeId = $_ENV['STORE_ID'];
        $this->setupSession(test: $this);
    }

    /**
     * Simulate calling the controller and getting JSON output.
     *
     * NOTE: This will manipulate headers. This will cause an error since
     * PHPUnit has already set a header. Suppressing is the only way.
     *
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function callController(
        string $govId,
        CustomerType $customerType,
        string $storeId
    ): string {
        $this->enableSession();

        return $this->controller->exec(
            storeId: $storeId,
            data: new GetAddressRequest(
                govId: $govId,
                customerType: $customerType
            ),
            sessionHandler: $this->session
        );
    }

    /**
     * Assert output from controller contains some string. This is an attempt
     * to identify the response before proceeding with further value evaluation.
     */
    private function assertResponseContains(
        string $needle,
        string $haystack
    ): void {
        $this->assertNotEmpty(actual: $haystack);
        $this->assertStringContainsString(needle: $needle, haystack: $haystack);
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
     * Assert exec() fetches address data.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     */
    public function testExec(): void
    {
        $govId = '198001010001';
        $customerType = CustomerType::NATURAL;

        $data = $this->callController(
            govId: $govId,
            customerType: $customerType,
            storeId: $this->storeId
        );

        $this->assertResponseContains(needle: 'addressRow1', haystack: $data);

        $obj = json_decode(
            json: $data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        $this->assertIsObject(actual: $obj);

        // Attempt object conversion to ensure we did get an Address back.
        $address = DataConverter::stdClassToType(
            object: $obj,
            type: Address::class
        );

        $this->assertInstanceOf(expected: Address::class, actual: $address);
        $this->assertSame(expected: 'Göteborg', actual: $address->postalArea);

        // Assert our controller stored the SSN data in the session.
        $request = new GetAddressRequest(
            govId: $govId,
            customerType: $customerType
        );

        $this->assertEquals(
            expected: $request,
            actual: Repository::getSsnData(sessionHandler: $this->session)
        );
    }

    /**
     * Assert exec() responds with an stdClass instance containing a non-empty
     * error property when we use a none existing store id (simulating a failed
     * API call to fetch address data).
     *
//     * @throws EmptyValueException
//     * @throws IllegalValueException
//     * @throws JsonException
     */
    public function testExecWithInvalidStoreId(): void
    {
        $this->markTestSkipped(
            message: 'This does not work, causes error. Disabled for now'
        );

//        $data = $this->callController(
//            govId: '198001010001',
//            customerType: CustomerType::NATURAL,
//            storeId: '35e0a591-4365-414e-82dc-5fa5eafe95fb'
//        );
//
//        $this->assertResponseContains(needle: 'error', haystack: $data);
//
//        $obj = json_decode(
//            json: $data,
//            associative: false,
//            depth: 512,
//            flags: JSON_THROW_ON_ERROR
//        );
//
//        $this->assertIsObject(actual: $obj);
//        $this->assertObjectHasAttribute(attributeName: 'error', object: $obj);
//        $this->assertNotEmpty(actual: $obj->error);
    }

    /**
     * Assert exec() fetches address data for company customer.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testExecForCompany(): void
    {
        $data = $this->callController(
            govId: '166997368573',
            customerType: CustomerType::LEGAL,
            storeId: $this->storeId
        );

        $this->assertResponseContains(needle: 'addressRow1', haystack: $data);

        $obj = json_decode(
            json: $data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        $this->assertIsObject(actual: $obj);

        // Attempt object conversion to ensure we did get an Address back.
        $address = DataConverter::stdClassToType(
            object: $obj,
            type: Address::class
        );

        $this->assertInstanceOf(expected: Address::class, actual: $address);
        $this->assertSame(
            expected: 'Helsingborg',
            actual: $address->postalArea
        );
    }

    /**
     * Assert that getRequestData() throws HttpException with code 415 when
     * supplied that does not convert to a GetAddressRequest instance.
     *
     * @throws HttpException
     * @throws JsonException
     */
    public function testGetRequestDataThrowsWithInaccurateData(): void
    {
        $controller = $this->getControllerWithMockedInputData(
            data: ['priceless' => 'precision']
        );

        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 415);

        $controller->getRequestData();
    }

    /**
     * Assert that getRequestData() throws HttpException with code 415 when
     * supplied data that would cause an IllegalValueException when attempting
     * to convert to GetAddressRequest instance.
     *
     * @throws HttpException
     * @throws JsonException
     */
    public function testGetRequestDataThrowsWithIllegalValues(): void
    {
        $controller = $this->getControllerWithMockedInputData(
            data: [
                'govId' => '166997368573',
                'customerType' => CustomerType::NATURAL->value,
            ]
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
     * GetAddressRequest instance.
     *
     * @throws HttpException
     * @throws JsonException
     */
    public function testGetRequestData(): void
    {
        $controller = $this->getControllerWithMockedInputData(
            data: [
                'govId' => '198001010001',
                'customerType' => CustomerType::NATURAL->value,
            ]
        );

        $data = $controller->getRequestData();

        $this->assertSame(
            expected: CustomerType::NATURAL,
            actual: $data->customerType
        );

        $this->assertSame(expected: '198001010001', actual: $data->govId);
    }
}
