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
        $response = new Request(5, ['foo' => 'bar'], 'baz');

        $this->assertEquals(5, $response->getID());
    }

    /**
     * @covers ::getParameters
     */
    public function testInstanceKeepsParameters()
    {
        $response = new Request(5, ['foo' => 'bar'], 'baz');

        $this->assertEquals(['foo' => 'bar'], $response->getParameters());
    }

    /**
     * @covers ::getStdin
     */
    public function testInstanceKeepsStdin()
    {
        $response = new Request(5, ['foo' => 'bar'], 'baz');

        $this->assertEquals('baz', $response->getStdin());
    }
}
