<?php
namespace Crunch\FastCGI\Protocol;

use Crunch\FastCGI\ReaderWriter\StringReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Protocol\Request
 * @covers \Crunch\FastCGI\Protocol\Request
 */
class RequestTest extends TestCase
{
    /**
     * @covers ::getRequestId
     */
    public function testInstanceKeepsId()
    {
        $response = new Request(5, new RequestParameters(['foo' => 'bar']), new StringReader('baz'));

        self::assertEquals(5, $response->getRequestId());
    }

    /**
     * @covers ::getParameters
     */
    public function testInstanceKeepsParameters()
    {
        $parameters = new RequestParameters(['foo' => 'bar']);
        $response = new Request(5, $parameters, new StringReader('baz'));

        self::assertSame($parameters, $response->getParameters());
    }

    /**
     * @covers ::getStdin
     */
    public function testInstanceKeepsStdin()
    {
        $response = new Request(5, new RequestParameters(['foo' => 'bar']), new StringReader('baz'));

        self::assertEquals('baz', $response->getStdin()->read(3));
    }
}
