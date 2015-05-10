<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Request
 * @covers \Crunch\FastCGI\Request
 */
class RequestTest extends TestCase
{
    /**
     * @covers ::getID
     */
    public function testInstanceKeepsId()
    {
        $response = new Request(5, new RequestParameters(['foo' => 'bar']), new StringReader('baz'));

        self::assertEquals(5, $response->getID());
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
