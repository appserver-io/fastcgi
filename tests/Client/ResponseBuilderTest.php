<?php
namespace Crunch\FastCGI\Client;

use Crunch\FastCGI\Protocol\RecordType;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Client\ResponseBuilder
 * @covers \Crunch\FastCGI\Client\ResponseBuilder
 */
class ResponseBuilderTest extends TestCase
{
    /**
     * @covers ::isComplete
     */
    public function testInitialResponseBuilderIsIncomplete()
    {
        $requestId = 42;
        $builder = new ResponseBuilder($requestId);

        self::assertFalse($builder->isComplete());
    }

    /**
     * @covers ::build
     */
    public function testExceptionOnIncompleteResponse()
    {
        $this->setExpectedException('\\RuntimeException');

        $requestId = 42;
        $builder = new ResponseBuilder($requestId);

        $builder->build();
    }

    /**
     * @covers ::addRecord
     */
    public function testPossibleToAddAnStdoutIsStillIncomplete()
    {
        $requestId = 42;
        $builder = new ResponseBuilder($requestId);

        $record = $this->prophesize('\Crunch\FastCGI\Protocol\Record');
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
        $requestId = 42;
        $builder = new ResponseBuilder($requestId);

        $record = $this->prophesize('\Crunch\FastCGI\Protocol\Record');
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
        $requestId = 42;
        $builder = new ResponseBuilder($requestId);

        $record = $this->prophesize('\Crunch\FastCGI\Protocol\Record');
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

        $requestId = 42;
        $builder = new ResponseBuilder($requestId);

        $record = $this->prophesize('\Crunch\FastCGI\Protocol\Record');
        $record->getType()->willReturn(RecordType::endRequest());
        $otherRecord = $this->prophesize('\Crunch\FastCGI\Protocol\Record');

        $builder->addRecord($record->reveal());
        $builder->addRecord($otherRecord->reveal());
    }
}
