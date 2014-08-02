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
     * @covers ::getVersion
     */
    public function testVersionIsConstantOne()
    {
        $record = new Record(2, 5, 'foo');

        $this->assertEquals(1, $record->getVersion());
    }

    /**
     * @covers ::getType
     */
    public function testInstanceKeepsType()
    {
        $record = new Record(2, 5, 'foo');

        $this->assertEquals(2, $record->getType());
    }

    /**
     * @covers ::getRequestId
     */
    public function testInstanceKeepsRequestId()
    {
        $record = new Record(2, 5, 'foo');

        $this->assertEquals(5, $record->getRequestId());
    }

    public function testInstanceKeepsBody()
    {
        $record = new Record(2, 5, 'foo');

        $this->assertEquals('foo', $record->getContent());
    }

    /**
     * @covers ::isSendable
     * @dataProvider sendableTypes
     * @param int $type
     */
    public function testRecordIsSendableTypes($type)
    {
        $record = new Record($type, 5, 'foo');

        $this->assertTrue($record->isSendable());
    }

    public static function sendableTypes()
    {
        return [
            [Record::BEGIN_REQUEST],
            [Record::ABORT_REQUEST],
            [Record::PARAMS],
            [Record::STDIN],
            [Record::DATA],
            [Record::GET_VALUES]
        ];
    }
}
