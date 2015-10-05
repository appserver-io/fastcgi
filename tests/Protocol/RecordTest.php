<?php
namespace Crunch\FastCGI\Protocol;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Protocol\Record
 * @covers \Crunch\FastCGI\Protocol\Record
 */
class RecordTest extends TestCase
{
    /**
     * @covers ::getType
     */
    public function testInstanceKeepsType()
    {
        $header = $this->prophesize('\Crunch\FastCGI\Protocol\Header');
        $header->getLength()->willReturn(3);
        $header->getType()->shouldBeCalled();

        $record = new Record($header->reveal(), 'foo');
        $record->getType();
    }

    /**
     * @covers ::getRequestId
     */
    public function testInstanceKeepsRequestId()
    {
        $header = $this->prophesize('\Crunch\FastCGI\Protocol\Header');
        $header->getLength()->willReturn(3);
        $header->getRequestId()->shouldBeCalled();

        $record = new Record($header->reveal(), 'foo');
        $record->getRequestId();
    }

    /**
     * @covers ::getContent
     */
    public function testInstanceKeepsBody()
    {
        $header = $this->prophesize('\Crunch\FastCGI\Protocol\Header');
        $header->getLength()->willReturn(3);

        $record = new Record($header->reveal(), 'foo');

        self::assertEquals('foo', $record->getContent());
    }

    public static function invalidPayloadTypes()
    {
        return [
            /* $payload */
            [null],
            [12],
            [new \stdClass()]
        ];
    }

    /**
     * @dataProvider invalidPayloadTypes
     * @uses \Crunch\FastCGI\Protocol\Header
     * @uses \Crunch\FastCGI\Protocol\RecordType
     * @param mixed $payload
     */
    public function testInvalidPayloadTypes($payload)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $header = new Header(RecordType::beginRequest(), 1, 2);

        new Record($header, $payload);
    }

    public static function payloadLengthMismatchExamples()
    {
        return [
            /* $length, $payload */
            [0, 'abc'],
            [3, ''],
            [2, 'abcdef'],
        ];
    }


    /**
     * @dataProvider payloadLengthMismatchExamples
     * @uses         \Crunch\FastCGI\Protocol\Header
     * @uses         \Crunch\FastCGI\Protocol\RecordType
     * @param integer $length
     * @param string  $payload
     */
    public function testPayloadLengthMismatch($length, $payload)
    {
        $this->setExpectedException('\LengthException');

        $header = new Header(RecordType::beginRequest(), 1, $length);

        new Record($header, $payload);
    }
}
