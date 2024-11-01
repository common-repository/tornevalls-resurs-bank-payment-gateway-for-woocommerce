<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Http;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\EcomTest\Data\Models\Instrument;

/**
 * Test basic controller methods.
 */
class ControllerTest extends TestCase
{
    private Controller $controller;

    protected function setUp(): void
    {
        parent::setUp();

        Config::setup();

        $this->controller = new Controller();
    }

    /**
     * Create a mocked version of the Controller class, setting the return value
     * of the getInputData method, in an effort to replicate behaviour with
     * incoming input data to PHP (faking the contents of php://input).
     */
    private function getControllerWithMockedInputData(string $data): Controller
    {
        $controller = $this->createPartialMock(
            originalClassName: Controller::class,
            methods: ['getInputData']
        );

        /** @noinspection PhpArgumentWithoutNamedIdentifierInspection */
        $controller->expects($this->once())
            ->method(constraint: 'getInputData')
            ->willReturn(value: $data);

        return $controller;
    }

    /**
     * Create a mocked version of the Controller class where the setHeader()
     * and setResponseCode() methods are never executed, making the respond()
     * method testable (since manipulating headers will break unit testing).
     *
     * This method also asserts that setHeader() is called twice, and that
     * setResponseCode() is called with the same code as supplied by $code.
     */
    private function getControllerWithoutHeaderManipulation(): Controller
    {
        return $this->createPartialMock(
            originalClassName: Controller::class,
            methods: ['log']
        );
    }

    /**
     * Assert respond() will echo JSON encoded data from supplied array.
     *
     * @throws JsonException
     */
    public function testRespond(): void
    {
        $data = ['some' => 'aha'];
        $controller = $this->getControllerWithoutHeaderManipulation();

        $result = $controller->respond(data: $data);

        $this->assertSame(
            expected: json_encode(value: $data, flags: JSON_THROW_ON_ERROR),
            actual: $result,
            message: 'Unexpected output data.'
        );
    }

    /**
     * Assert respondWithError() will echo JSON encoded data including Exception
     * message. Also asserts that the http response code matching the expected
     * error code.
     *
     * @throws JsonException
     */
    public function testRespondWithError(): void
    {
        $data = ['error' => 'Magic math'];
        $controller = $this->getControllerWithoutHeaderManipulation();

        $result = $controller->respondWithError(
            exception: new HttpException(message: 'Magic math', code: 418)
        );

        $this->assertSame(
            expected: json_encode(value: $data, flags: JSON_THROW_ON_ERROR),
            actual: $result,
            message: 'Unexpected output data.'
        );
    }

    /**
     * Assert getErrorMessage() returns unmasked error message for HttpException.
     */
    public function testGetErrorMessageReturnsUnmasked(): void
    {
        $this->assertSame(
            expected: 'My Message',
            actual: $this->controller->getErrorMessage(
                exception: new HttpException(message: 'My Message')
            ),
            message: 'Error message is not unmasked.'
        );
    }

    /**
     * Test getErrorMessage() will mask Exception messages.
     */
    public function testGetErrorMessageReturnsMasked(): void
    {
        $this->assertSame(
            expected: 'An unknown error occurred.',
            actual: $this->controller->getErrorMessage(
                exception: new Exception(message: 'My Message')
            )
        );
    }

    /**
     * Assert log() does not throw Exceptions.
     */
    public function testLogIsSilent(): void
    {
        Config::unsetInstance();

        $this->expectNotToPerformAssertions();
        $this->controller->log(exception: new Exception(message: 'Nothing'));
    }

    /**
     * Assert translateError() provides defaulted message if translation fails.
     */
    public function testDefaultError(): void
    {
        $this->assertSame(
            expected: 'Failed to translate error. Check debug log for info.',
            actual: $this->controller->translateError(
                phraseId: 'some-undefined-translation-995'
            ),
            message: 'Unexpected default error message.'
        );
    }

    /**
     * Assert getErrorResponseCode extract HTTP response code from CurlException
     * instance.
     */
    public function getErrorResponseCodeFromCurlException(): void
    {
        $this->assertSame(
            expected: 404,
            actual: $this->controller->getErrorResponseCode(
                exception: new CurlException(
                    message: 'nothing',
                    code: 1,
                    httpCode: 404,
                    body: false
                )
            ),
            message: 'Failed to resolve HTTP response code from Curl Exception'
        );
    }

    /**
     * Assert getErrorResponseCode extract HTTP response code from HttpException
     * instance.
     */
    public function getErrorResponseCodeFromHttpException(): void
    {
        $this->assertSame(
            expected: 411,
            actual: $this->controller->getErrorResponseCode(
                exception: new HttpException(
                    message: 'nothing',
                    code: 411
                )
            ),
            message: 'Failed to resolve HTTP response code from Http Exception'
        );
    }

    /**
     * Assert getErrorResponseCode defaults to 400.
     */
    public function getErrorResponseCodeDefaultsTo400(): void
    {
        $this->assertSame(
            expected: 400,
            actual: $this->controller->getErrorResponseCode(
                exception: new Exception(
                    message: 'nothing',
                    code: 411
                )
            ),
            message: 'Failed to resolve HTTP response code from Exception'
        );
    }

    /**
     * Assert getInputData() throws HttpException with code 400 when there is
     * no input data present.
     *
     * @throws HttpException
     */
    public function testGetInputDataThrowsWithoutData(): void
    {
        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 400);
        $this->controller->getInputData();
    }

    /**
     * Assert getRequestModel() throws HttpException with code 406 when input
     * data is not correctly formatted JSON.
     *
     * @throws HttpException
     */
    public function testGetRequestModelThrowsWithoutJson(): void
    {
        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 406);
        $this->getControllerWithMockedInputData(
            data: 'nope'
        )->getRequestModel(model: Instrument::class);
    }

    /**
     * Assert getRequestModel() throws HttpException with code 406 when input
     * data is does not resolve to an stdClass instance.
     *
     * @throws HttpException
     */
    public function testGetRequestModelThrowsWithoutObject(): void
    {
        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 406);
        $this->getControllerWithMockedInputData(
            data: '[1,2,3]'
        )->getRequestModel(model: Instrument::class);
    }

    /**
     * Assert getRequestModel() throws HttpException with code 415 when input
     * data is does not resolve to request model class instance.
     *
     * @throws HttpException
     */
    public function testGetRequestModelThrowsWithoutModel(): void
    {
        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 415);
        $this->getControllerWithMockedInputData(
            data: '{"wonky": "donkey"}'
        )->getRequestModel(model: Instrument::class);
    }

    /**
     * Assert getRequestModel() throws HttpException with code 415 when input
     * data contains a property whose datatype is incompatible with the
     * corresponding property on the supplied model class.
     *
     * @throws HttpException
     */
    public function testGetRequestModelRespectsDataTypes(): void
    {
        $this->expectException(exception: HttpException::class);
        $this->expectExceptionCode(code: 415);
        $this->getControllerWithMockedInputData(
            data: '{"id": "5", "name": "Bow"}'
        )->getRequestModel(model: Instrument::class);
    }

    /**
     * Assert getRequestModel() converts input data to model.
     *
     * @throws HttpException
     */
    public function testGetRequestModel(): void
    {
        $result = $this->getControllerWithMockedInputData(
            data: '{"id": 5, "name": "Bow"}'
        )->getRequestModel(model: Instrument::class);

        $this->assertInstanceOf(expected: Instrument::class, actual: $result);
        $this->assertEquals(
            expected: new Instrument(id: 5, name: 'Bow'),
            actual: $result
        );
    }
}
