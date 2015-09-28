<?php
namespace Crunch\FastCGI\Protocol;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Protocol\RequestParameters
 * @covers \Crunch\FastCGI\Protocol\RequestParameters
 */
class RequestParametersTest extends TestCase
{
    /**
     * @uses \Crunch\FastCGI\Protocol\Record
     * @uses \Crunch\FastCGI\Protocol\RecordType
     * @uses \Crunch\FastCGI\Protocol\Header
     * @covers ::encode
     */
    public function testEncodeEmptyParameters()
    {
        $parameters = new RequestParameters([]);

        $records = $parameters->encode(123);

        self::assertCount(1, $records);
        self::assertEquals(123, $records[0]->getRequestId());
        self::assertSame(RecordType::params(), $records[0]->getType());
        self::assertEquals('', $records[0]->getContent());
    }
}
