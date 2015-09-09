<?php
namespace Crunch\FastCGI\Protocol;

use Crunch\FastCGI\ReaderWriter\StringReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @uses \Crunch\FastCGI\Protocol\Role
 * @uses \Crunch\FastCGI\Protocol\RequestParameters
 * @uses \Crunch\FastCGI\ReaderWriter\StringReader
 * @coversDefaultClass \Crunch\FastCGI\Protocol\Request
 * @covers \Crunch\FastCGI\Protocol\Request
 */
class RequestTest extends TestCase
{
    /**
     * @covers ::getRole
     */
    public function testInstanceKeepsRole()
    {
        $request = new Request(Role::responder(), 5, false, new RequestParameters(['foo' => 'bar']), new StringReader('baz'));

        self::assertSame(Role::responder(), $request->getRole());
    }

    /**
     * @covers ::getRequestId
     */
    public function testInstanceKeepsId()
    {
        $request = new Request(Role::responder(), 5, false, new RequestParameters(['foo' => 'bar']), new StringReader('baz'));

        self::assertEquals(5, $request->getRequestId());
    }

    /**
     * @covers ::isKeepConnection
     */
    public function testInstanceKeepsKeepConnection()
    {
        $request = new Request(Role::responder(), 5, false, new RequestParameters(['foo' => 'bar']), new StringReader('baz'));

        self::assertFalse($request->isKeepConnection());
    }

    /**
     * @covers ::getParameters
     */
    public function testInstanceKeepsParameters()
    {
        $parameters = new RequestParameters(['foo' => 'bar']);
        $request = new Request(Role::responder(), 5, false, $parameters, new StringReader('baz'));

        self::assertSame($parameters, $request->getParameters());
    }

    /**
     * @covers ::getStdin
     */
    public function testInstanceKeepsStdin()
    {
        $request = new Request(Role::responder(), 5, false, new RequestParameters(['foo' => 'bar']), new StringReader('baz'));

        self::assertEquals('baz', $request->getStdin()->read(3));
    }
}
