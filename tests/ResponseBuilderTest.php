<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\ResponseBuilder
 * @covers \Crunch\FastCGI\ResponseBuilder
 */
class ResponseBuilderTest extends TestCase
{
    public function testInitialResponseBuilderIsIncomplete()
    {
        $builder = new ResponseBuilder;

        $this->assertFalse($builder->isComplete());
    }
}
