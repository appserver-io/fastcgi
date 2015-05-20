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
}
