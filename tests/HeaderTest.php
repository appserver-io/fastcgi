<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Header
 * @covers \Crunch\FastCGI\Header
 */
class HeaderTest extends TestCase
{
    public function testConstructKeepsValues()
    {
        $header = new Header(1, 2, 3, 4, 4);

        self::assertEquals(1, $header->getVersion());
        self::assertEquals(2, $header->getType());
        self::assertEquals(3, $header->getRequestId());
        self::assertEquals(4, $header->getLength());
        self::assertEquals(4, $header->getPaddingLength());
        self::assertEquals(8, $header->getPayloadLength());
    }

    public static function invalidConstructorArguments()
    {
        return [
            /* $version, $type, $requestId, $length, $padding */
            [12, 4, 12, 4, null],
            [-1, 4, 12, 4, null],
            [1, -4, 12, 4, null],
            [1, 55, 12, 4, null],
            [1, 4, -9, 4, null],
            [1, 4, 0, 4, null],
            [1, 4, 12, 4, 9],
            [1, 4, 12, 4, 2],
        ];
    }

    /**
     * @dataProvider invalidConstructorArguments
     * @param int $version
     * @param int $type
     * @param int $requestId
     * @param int $length
     * @param int|null $padding
     */
    public function testInvalidConstructorArguments($version, $type, $requestId, $length, $padding)
    {
        $this->setExpectedException('\Assert\AssertionFailedException');

        new Header($version, $type, $requestId, $length, $padding);
    }

    // TODO test invalid values (exception)

    public static function lengthAndPaddingProvider()
    {
        return [
            /* $length, $expectedPadding */
            [0, 0],
            [4, 4],
            [1, 7],
            [8, 0],
        ];
    }

    /**
     * @dataProvider lengthAndPaddingProvider
     * @param int $length
     * @param int $expectedPadding
     */
    public function testCorrectPaddingCalculation($length, $expectedPadding)
    {
        $header = new Header(1, 2, 3, $length);

        self::assertEquals($expectedPadding, $header->getPaddingLength());
    }

    public static function encodedHeaderProvider()
    {
        /*
         * First byte "version"
         * Second byte "type"
         * Byte 3 and 4 "request id"
         * Byte 5 and 6 "length"
         * Byte 7 "paddingLength"
         * Byte 8 "unused" (still required)
         */
        return [
            /* $header, $version, $type, $requestId, $length, $paddingLength */
            ["\x01\x02\x00\x03\x00\x04\x04\x00", 1, 2, 3, 4, 4],
            ["\x01\x06\x00\x12\x00\x10\x00\x00", 1, 6, 18, 16, 0],
            ["\x01\x06\x00\x12\x1f\xa4\x04\x00", 1, 6, 18, 8100, 4],
        ];
    }

    /**
     * @dataProvider encodedHeaderProvider
     * @param string $headerString
     * @param int $version
     * @param int $type
     * @param int $requestId
     * @param int $length
     * @param int $paddingLength
     */
    public function testDecodeHeader($headerString, $version, $type, $requestId, $length, $paddingLength)
    {
        $header = Header::decode($headerString);

        self::assertEquals($version, $header->getVersion());
        self::assertEquals($type, $header->getType());
        self::assertEquals($requestId, $header->getRequestId());
        self::assertEquals($length, $header->getLength());
        self::assertEquals($paddingLength, $header->getPaddingLength());
        self::assertEquals($length + $paddingLength, $header->getPayloadLength());
        self::assertEquals(0, $header->getPayloadLength() % 8);
    }

    /**
     * @dataProvider encodedHeaderProvider
     * @param string $headerString
     * @param int $version
     * @param int $type
     * @param int $requestId
     * @param int $length
     * @param int $paddingLength
     */
    public function testEncodeHeader($headerString, $version, $type, $requestId, $length, $paddingLength)
    {
        $header = new Header($version, $type, $requestId, $length, $paddingLength);

        $this->assertEquals($headerString, $header->encode());
    }

    public static function invalidHeaderStrings()
    {
        return [
            /* $headerString */

            // to short
            ["\x01"],
            ["\x01\x02"],
            ["\x01\x02\x00"],
            ["\x01\x02\x00\x03"],
            ["\x01\x02\x00\x03\x00"],
            ["\x01\x02\x00\x03\x00\x04"],
            ["\x01\x02\x00\x03\x00\x04\x05"],

            // to long
            ["\x01\x02\x00\x03\x00\x04\x04\x06\x07"],
            ["\x01\x02\x00\x03\x00\x04\x04\x06\x07\x08"],

            ["\x01\x02\x00\x03\x00\x04\x09\x00"], // Invalid padding
            ["\x04\x02\x00\x03\x00\x04\x04\x00"], // Invalid version
            ["\x01\x0E\x00\x03\x00\x04\x04\x00"], // Invalid type

        ];
    }

    /**
     * @dataProvider invalidHeaderStrings
     * @param string $headerString
     */
    public function testInvalidHeaderStrings($headerString)
    {
        $this->setExpectedException('\Assert\AssertionFailedException');

        Header::decode($headerString);
    }
}
