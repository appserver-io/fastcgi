<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Response
 * @covers \Crunch\FastCGI\Response
 */
class ResponseTest extends TestCase
{
    /**
     * @covers ::getError
     */
    public function testInstanceKeepsError()
    {
        $response = new Response('foo', 'bar');

        self::assertEquals('bar', $response->getError());
    }

    /**
     * @covers ::getContent
     */
    public function testInstanceKeepsContent()
    {
        $response = new Response('foo', 'bar');

        self::assertEquals('foo', $response->getContent());
    }
}
