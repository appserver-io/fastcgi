<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\ResponseBuilder
 * @covers \Crunch\FastCGI\ResponseBuilder
 */
class ResponseBuilderTest extends TestCase
{
    /**
     * @covers ::isComplete
     */
    public function testInitialResponseBuilderIsIncomplete()
    {
        $builder = new ResponseBuilder;

        $this->assertFalse($builder->isComplete());
    }

    /**
     * @covers ::build
     */
    public function testExceptionOnIncompleteResponse()
    {
        $this->setExpectedException('\\RuntimeException');

        $builder = new ResponseBuilder;

        $builder->build();
    }

    /**
     * @covers ::addRecord
     */
    public function testPossibleToAddAnStdoutIsStillIncomplete()
    {
        $builder = new ResponseBuilder;

        $record = $this->prophesize('\Crunch\FastCGI\Record');
        $record->getType()->willReturn(Record::STDOUT);
        $record->getContent()->willReturn('foo');

        $builder->addRecord($record->reveal());

        $this->assertFalse($builder->isComplete());
    }

    /**
     * @covers ::addRecord
     */
    public function testPossibleToAddAnStderrIsStillIncomplete()
    {
        $builder = new ResponseBuilder;

        $record = $this->prophesize('\Crunch\FastCGI\Record');
        $record->getType()->willReturn(Record::STDERR);
        $record->getContent()->willReturn('foo');

        $builder->addRecord($record->reveal());

        $this->assertFalse($builder->isComplete());
    }

    /**
     * @covers ::addRecord
     */
    public function testPossibleToAddAnEndRequestCompletesRequest()
    {
        $builder = new ResponseBuilder;

        $record = $this->prophesize('\Crunch\FastCGI\Record');
        $record->getType()->willReturn(Record::END_REQUEST);

        $builder->addRecord($record->reveal());

        $this->assertTrue($builder->isComplete());
    }

    /**
     * @covers ::addRecord
     */
    public function testCannotAddRecordToComplete()
    {
        $this->setExpectedException('\RuntimeException');

        $builder = new ResponseBuilder;

        $record = $this->prophesize('\Crunch\FastCGI\Record');
        $record->getType()->willReturn(Record::END_REQUEST);
        $otherRecord = $this->prophesize('\Crunch\FastCGI\Record');

        $builder->addRecord($record->reveal());
        $builder->addRecord($otherRecord->reveal());
    }
}
