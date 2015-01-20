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
     * @covers ::buildResponse
     */
    public function testExceptionOnIncompleteResponse()
    {
        $this->setExpectedException('\\RuntimeException');

        $builder = new ResponseBuilder;

        $builder->buildResponse();
    }

    /**
     * @covers ::addRecord
     */
    public function testPossibleToAddAnStdoutIsStillIncomplete()
    {
        $builder = new ResponseBuilder;

        $record = \Phake::mock('\Crunch\FastCGI\Record');
        \Phake::when($record)->getType()->thenReturn(Record::STDOUT);
        \Phake::when($record)->getContent()->thenReturn('foo');

        $builder->addRecord($record);

        $this->assertFalse($builder->isComplete());
    }

    /**
     * @covers ::addRecord
     */
    public function testPossibleToAddAnStderrIsStillIncomplete()
    {
        $builder = new ResponseBuilder;

        $record = \Phake::mock('\Crunch\FastCGI\Record');
        \Phake::when($record)->getType()->thenReturn(Record::STDERR);
        \Phake::when($record)->getContent()->thenReturn('foo');

        $builder->addRecord($record);

        $this->assertFalse($builder->isComplete());
    }

    /**
     * @covers ::addRecord
     */
    public function testPossibleToAddAnEndRequestCompletesRequest()
    {
        $builder = new ResponseBuilder;

        $record = \Phake::mock('\Crunch\FastCGI\Record');
        \Phake::when($record)->getType()->thenReturn(Record::END_REQUEST);

        $builder->addRecord($record);

        $this->assertTrue($builder->isComplete());
    }

    /**
     * @covers ::addRecord
     */
    public function testCannotAddRecordToComplete()
    {
        $this->setExpectedException('\RuntimeException');

        $builder = new ResponseBuilder;

        $record = \Phake::mock('\Crunch\FastCGI\Record');
        \Phake::when($record)->getType()->thenReturn(Record::END_REQUEST);
        $otherRecord = \Phake::mock('\Crunch\FastCGI\Record');

        $builder->addRecord($record);
        $builder->addRecord($otherRecord);
    }
}
