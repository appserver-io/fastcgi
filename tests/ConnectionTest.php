<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \Crunch\FastCGI\Connection
 * @covers \Crunch\FastCGI\Connection
 */
class ConnectionTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $socket;
    /** @var ObjectProphecy */
    private $recordHandler;

    protected function setUp()
    {
        parent::setUp();

        $this->socket = $this->prophesize('\Socket\Raw\Socket');

        $this->recordHandler = $this->prophesize('\Crunch\FastCGI\RecordHandlerInterface');
    }

    public function testSocketNotReadyWhileSending()
    {
        $this->socket->selectWrite(Argument::type('int'))->willReturn(false);

        $this->setExpectedException('\Exception');
        $connection = new Connection($this->socket->reveal(), $this->recordHandler->reveal());


    }
}
