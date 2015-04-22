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

        self::assertFalse($builder->isComplete());
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
        $record->getType()->willReturn(RecordType::stdout());
        $record->getContent()->willReturn('foo');

        $builder->addRecord($record->reveal());

        self::assertFalse($builder->isComplete());
    }

    /**
     * @covers ::addRecord
     */
    public function testPossibleToAddAnStderrIsStillIncomplete()
    {
        $builder = new ResponseBuilder;

        $record = $this->prophesize('\Crunch\FastCGI\Record');
        $record->getType()->willReturn(RecordType::stderr());
        $record->getContent()->willReturn('foo');

        $builder->addRecord($record->reveal());

        self::assertFalse($builder->isComplete());
    }

    /**
     * @covers ::addRecord
     */
    public function testPossibleToAddAnEndRequestCompletesRequest()
    {
        $builder = new ResponseBuilder;

        $record = $this->prophesize('\Crunch\FastCGI\Record');
        $record->getType()->willReturn(RecordType::endRequest());

        $builder->addRecord($record->reveal());

        self::assertTrue($builder->isComplete());
    }

    /**
     * @covers ::addRecord
     */
    public function testCannotAddRecordToComplete()
    {
        $this->setExpectedException('\RuntimeException');

        $builder = new ResponseBuilder;

        $record = $this->prophesize('\Crunch\FastCGI\Record');
        $record->getType()->willReturn(RecordType::endRequest());
        $otherRecord = $this->prophesize('\Crunch\FastCGI\Record');

        $builder->addRecord($record->reveal());
        $builder->addRecord($otherRecord->reveal());
    }
}
