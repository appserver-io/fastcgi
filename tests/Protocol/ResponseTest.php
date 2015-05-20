<?php
namespace Crunch\FastCGI\Protocol;

use Crunch\FastCGI\ReaderWriter\StringReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Protocol\Response
 * @covers \Crunch\FastCGI\Protocol\Response
 */
class ResponseTest extends TestCase
{
    /**
     * @covers ::getError
     */
    public function testInstanceKeepsError()
    {
        $content = new StringReader('foo');
        $error = new StringReader('bar');
        $response = new Response($content, $error);

        self::assertSame($error, $response->getError());
    }

    /**
     * @covers ::getContent
     */
    public function testInstanceKeepsContent()
    {
        $content = new StringReader('foo');
        $error = new StringReader('bar');
        $response = new Response($content, $error);

        self::assertSame($content, $response->getContent());
    }
}
