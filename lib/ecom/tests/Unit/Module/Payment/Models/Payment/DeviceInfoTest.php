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
//use Resursbank\Ecom\Module\Payment\Models\Payment\DeviceInfo as DeviceInfoModel;
//use stdClass;

/**
 * Test data integrity of DeviceInfo entity model.
 */
class DeviceInfoTest extends TestCase
{
//    /**
//     * @var DeviceInfoModel
//     */
//    private DeviceInfoModel $item;
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
//            type: DeviceInfoModel::class
//        );
//
//        if (!$item instanceof DeviceInfoModel) {
//            throw new TestException(
//                message: 'Conversion succeeded but did not return ' .
//                    'Order Line instance.'
//            );
//        }
//
//        $this->item = $item;
//    }

//    /**
//     * Assert validateUserAgent() throws IllegalValueException when its
//     * length is too long.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidateUserAgentThrowsWhenTooLong(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'userAgent' => 'Lorem ipsum dolor sit amet, consectetur ' .
//                'adipiscing elit. Curabitur fringilla, leo ut maximus.' .
//                'accumsan, massa nulla ornare arcu. Lorem ipsum dolor sit. ' .
//                'met Lorem ipsum dolor sit amet, consectetur. Curabitur ' .
//                'fringilla, leo ut maximus.'
//        ]);
//    }
//
//    /**
//     * Assert validateUserAgent() throws IllegalValueException when its
//     * length is too short.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidateUserAgentThrowsWhenTooShort(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'userAgent' => ''
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
//    public function testUserAgentWasAssigned(): void
//    {
//        $this->convert();
//        $this->assertSame(
//            expected: $this->data->userAgent,
//            actual: $this->item->userAgent
//        );
//    }
}
