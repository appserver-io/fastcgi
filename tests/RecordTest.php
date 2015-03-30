<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Record
 * @covers \Crunch\FastCGI\Record
 */
class RecordTest extends TestCase
{
    /**
     * @covers ::getType
     */
    public function testInstanceKeepsType()
    {
        $header = $this->prophesize('\Crunch\FastCGI\Header');

        $record = new Record($header->reveal(), 'foo');
        $record->getType();

        $header->getType()->shouldHaveBeenCalled();
    }

    /**
     * @covers ::getRequestId
     */
    public function testInstanceKeepsRequestId()
    {
        $header = $this->prophesize('\Crunch\FastCGI\Header');

        $record = new Record($header->reveal(), 'foo');
        $record->getRequestId();

        $header->getRequestId()->shouldHaveBeenCalled();
    }

    /**
     * @covers ::getContent
     */
    public function testInstanceKeepsBody()
    {
        $header = $this->prophesize('\Crunch\FastCGI\Header');

        $record = new Record($header->reveal(), 'foo');

        $this->assertEquals('foo', $record->getContent());
    }
}
