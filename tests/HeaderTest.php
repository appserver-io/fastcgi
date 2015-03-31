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
        $header = new Header(1, 2, 3, 4, 5);

        self::assertEquals(1, $header->getVersion());
        self::assertEquals(2, $header->getType());
        self::assertEquals(3, $header->getRequestId());
        self::assertEquals(4, $header->getLength());
        self::assertEquals(5, $header->getPaddingLength());
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

    // TODO test invalid length/padding combinations (exception)

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
            ["\x01\x02\x00\x03\x00\x04\x05\x00", 1, 2, 3, 4, 5]
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
    }

    // TODO test invalid header strings
    // TODO test encode header
    // TODO test types/invalid types (exception)
}
