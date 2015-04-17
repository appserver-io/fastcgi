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
    private $socketProphet;

    protected function setUp()
    {
        parent::setUp();

        $this->socketProphet = $this->prophesize('\Socket\Raw\Socket');
        $this->socketProphet->close()->willReturn(null);
    }

    public function testSocketNotReadyWhileSending()
    {
        $recordProphet = $this->prophesize('\Crunch\FastCGI\Record');

        $this->socketProphet->selectWrite(Argument::type('int'))->willReturn(false);

        $this->setExpectedException('\Crunch\FastCGI\ConnectionException');
        $connection = new Connection($this->socketProphet->reveal());

        $connection->send($recordProphet->reveal());
    }
}
