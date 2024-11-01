<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Payment\Models\Payment;

//use JsonException;
use PHPUnit\Framework\TestCase;

//use ReflectionException;
//use Resursbank\Ecom\Exception\TestException;
//use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
//use Resursbank\Ecom\Exception\Validation\IllegalValueException;
//use Resursbank\Ecom\Lib\Utilities\DataConverter;
//use Resursbank\EcomTest\Data\OrderLine;
//use Resursbank\Ecom\Module\Payment\Models\Payment\DeliveryAddress as AddressModel;
//use stdClass;

/**
 * Test data integrity of Address entity model.
 */
class AddressTest extends TestCase
{
//    /**
//     * @var AddressModel
//     */
//    private AddressModel $item;
//
//    /**
//     * @var stdClass
//     */
//    private stdClass $data;
//
//    /**
//     * @return void
//     * @throws JsonException
//     * @throws TestException
//     */
//    protected function setUp(): void
//    {
//        $this->data = OrderLine::getRandomData();
//
//        parent::setUp();
//    }

//    /**
//     * @param array $updates
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    private function convert(
//        array $updates = []
//    ): void {
//        foreach ($updates as $key => $val) {
//            $this->data->{$key} = $val;
//        }
//
//        $item = DataConverter::stdClassToType(
//            object: $this->data,
//            type: AddressModel::class
//        );
//
//        if (!$item instanceof AddressModel) {
//            throw new TestException(
//                message: 'Conversion succeeded but did not return ' .
//                    'Order Line instance.'
//            );
//        }
//
//        $this->item = $item;
//    }

//    /**
//     * Assert validateFullName() throws IllegalValueException when its
//     * length is too long.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidateFullNameThrowsWhenTooLong(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'fullName' => 'Lorem ipsum dolor sit amet, consectetur ' .
//                'adipiscing elit. Curabitur fringilla, leo ut maximus.' .
//                'accumsan, massa nulla ornare arcu.'
//        ]);
//    }
//
//    /**
//     * Assert validateFirstName() throws IllegalValueException when its
//     * length is too long.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidateFirstNameThrowsWhenTooLong(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'firstName' => 'Lorem ipsum dolor sit amet, consectetur ' .
//                'adipiscing elit. Curabitur fringilla, leo ut maximus.' .
//                'accumsan, massa nulla ornare arcu.'
//        ]);
//    }
//
//    /**
//     * Assert validateLastName() throws IllegalValueException when its
//     * length is too long.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidateLastNameThrowsWhenTooLong(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'lastName' => 'Lorem ipsum dolor sit amet, consectetur ' .
//                'adipiscing elit. Curabitur fringilla, leo ut maximus.' .
//                'accumsan, massa nulla ornare arcu.'
//        ]);
//    }
//
//    /**
//     * Assert validateAddressRow1() throws IllegalValueException when its
//     * length is too short.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidateAddressRow1ThrowsWhenTooShort(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'addressRow1' => ''
//        ]);
//    }
//
//    /**
//     * Assert validateAddressRow1() throws IllegalValueException when its
//     * length is too long.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidateAddressRow1ThrowsWhenTooLong(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'addressRow1' => 'Lorem ipsum dolor sit amet, consectetur ' .
//                'adipiscing elit. Curabitur fringilla, leo ut maximus.' .
//                'accumsan, massa nulla ornare arcu.'
//        ]);
//    }
//
//    /**
//     * Assert validateAddressRow2() throws IllegalValueException when its
//     * length is too long.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidateAddressRow2ThrowsWhenTooLong(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'addressRow2' => 'Lorem ipsum dolor sit amet, consectetur ' .
//                'adipiscing elit. Curabitur fringilla, leo ut maximus.' .
//                'accumsan, massa nulla ornare arcu.'
//        ]);
//    }
//
//    /**
//     * Assert validatePostalArea() throws IllegalValueException when its
//     * length is too short.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidatePostalAreaThrowsWhenTooShort(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'postalArea' => ''
//        ]);
//    }
//
//    /**
//     * Assert validatePostalArea() throws IllegalValueException when its
//     * length is too long.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidatePostalAreaThrowsWhenTooLong(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'postalArea' => 'Lorem ipsum dolor sit amet, consectetur ' .
//                'adipiscing elit. Curabitur fringilla, leo ut maximus.' .
//                'accumsan, massa nulla ornare arcu.'
//        ]);
//    }
//
//    /**
//     * Assert validatePostalCode() throws IllegalValueException when its
//     * length is too short.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidatePostalCodeThrowsWhenTooShort(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'postalCode' => ''
//        ]);
//    }
//
//    /**
//     * Assert validatePostalCode() throws IllegalValueException when its
//     * length is too long.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidatePostalCodeThrowsWhenTooLong(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'postalCode' => 'Lorem ipsum dolor sit amet, consectetur ' .
//                'adipiscing elit. Curabitur fringilla, leo ut maximus.' .
//                'accumsan, massa nulla ornare arcu.'
//        ]);
//    }
//
//    /**
//     * Assert validatePostalCode() throws IllegalValueException when it does not
//     * contain integers.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidatePostalCodeThrowsWhenItsNotNumeric(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'postalCode' => 'Lorem'
//        ]);
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testFullNameWasAssigned(): void
//    {
//        $this->convert();
//        $this->assertSame(
//            expected: $this->data->fullName,
//            actual: $this->item->fullName
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testFirstNameWasAssigned(): void
//    {
//        $this->convert();
//        $this->assertSame(
//            expected: $this->data->firstName,
//            actual: $this->item->firstName
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testLastNameWasAssigned(): void
//    {
//        $this->convert();
//        $this->assertSame(
//            expected: $this->data->lastName,
//            actual: $this->item->lastName
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testAddressRow1WasAssigned(): void
//    {
//        $this->convert();
//        $this->assertSame(
//            expected: $this->data->addressRow1,
//            actual: $this->item->addressRow1
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testAddressRow2WasAssigned(): void
//    {
//        $this->convert();
//        $this->assertSame(
//            expected: $this->data->addressRow2,
//            actual: $this->item->addressRow2
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testPostalAreaWasAssigned(): void
//    {
//        $this->convert();
//        $this->assertSame(
//            expected: $this->data->postalArea,
//            actual: $this->item->postalArea
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testPostalCodeWasAssigned(): void
//    {
//        $this->convert();
//        $this->assertSame(
//            expected: $this->data->postalCode,
//            actual: $this->item->postalCode
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testCountryCodeWasAssigned(): void
//    {
//        $this->convert();
//        $this->assertSame(
//            expected: $this->data->countryCode,
//            actual: $this->item->countryCode
//        );
//    }
}
