<?php
namespace Crunch\FastCGI\Connection;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \Crunch\FastCGI\Connection\Connection
 * @covers \Crunch\FastCGI\Connection\Connection
 */
class ConnectionTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $socketProphet;

    protected function setUp()
    {
        TestCase::setUp();

        $this->socketProphet = $this->prophesize('\Socket\Raw\Socket');
        $this->socketProphet->close()->willReturn(null);
    }

    /**
     * @covers ::send
     */
    public function testSocketNotReadyWhileSending()
    {
        $recordProphet = $this->prophesize('\Crunch\FastCGI\Protocol\Record');

        $this->socketProphet->selectWrite(Argument::type('int'))->willReturn(false);

        $this->setExpectedException('\Crunch\FastCGI\Connection\ConnectionException');
        $connection = new Connection($this->socketProphet->reveal());

        $connection->send($recordProphet->reveal());
    }

    /**
     * @covers ::send
     */
    public function testSendARecord()
    {
        $recordProphet = $this->prophesize('\Crunch\FastCGI\Protocol\Record');
        $recordProphet->encode()->willReturn('foo');
        $record = $recordProphet->reveal();
        $this->socketProphet->send('foo', Argument::type('integer'))->shouldBeCalled();

        $this->socketProphet->selectWrite(Argument::type('int'))->willReturn(true);

        $connection = new Connection($this->socketProphet->reveal());

        $connection->send($record);
    }

    /**
     * @covers ::receive
     */
    public function testReceiveWhileSocketNotReady()
    {
        $this->socketProphet
            ->selectRead(Argument::type('integer'))
            ->willReturn(false);
        $socket = $this->socketProphet->reveal();

        $connection = new Connection($socket);

        self::assertNull($connection->receive(0));
    }

    /**
     * @covers ::receive
     */
    public function testReceiveSomethingIncomplete()
    {
        $this->socketProphet
            ->selectRead(Argument::type('integer'))
            ->willReturn(true);
        $this->socketProphet
            ->recv(Argument::type('integer'), Argument::type('integer'))
            ->willReturn(false);
        $socket = $this->socketProphet->reveal();

        $connection = new Connection($socket);

        self::assertNull($connection->receive(0));
    }
}
