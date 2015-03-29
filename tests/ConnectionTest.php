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

    protected function setUp()
    {
        parent::setUp();

        $this->socket = $this->prophesize('\Socket\Raw\Socket');
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     */
    public function testCreateNewRequest()
    {
        $connection = new Connection($this->socket->reveal(), new BuilderFactory);

        $request = $connection->newRequest(['some' => 'param'], 'foobar');

        $this->assertInstanceOf('\Crunch\FastCGI\Request', $request);
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     * @uses \Crunch\FastCGI\Request::getID
     */
    public function testNewInstanceHasIntegerId()
    {
        $connection = new Connection($this->socket->reveal(), new BuilderFactory);

        $request = $connection->newRequest(['some' => 'param'], 'foobar');

        $this->assertInternalType('integer', $request->getID());
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     * @uses \Crunch\FastCGI\Request::getParameters
     */
    public function testNewInstanceKeepsParameters()
    {
        $connection = new Connection($this->socket->reveal(), new BuilderFactory);

        $request = $connection->newRequest(['some' => 'param'], 'foobar');

        $this->assertEquals(['some' => 'param'], $request->getParameters());
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     * @uses \Crunch\FastCGI\Request::getStdin
     */
    public function testNewInstanceKeepsBody()
    {
        $connection = new Connection($this->socket->reveal(), new BuilderFactory);

        $request = $connection->newRequest(['some' => 'param'], 'foobar');

        $this->assertEquals('foobar', $request->getStdin());
    }

    /**
     * @covers ::sendRequest
     * @uses \Crunch\FastCGI\Record
     */
    public function testSendRequest()
    {
        $this->socket->selectWrite(Argument::any())->willReturn(true);
        $this->socket->send(Argument::type('string'), Argument::type('int'))->shouldBeCalled();
        $this->socket->close()->willReturn(null);
        $request = $this->prophesize('\Crunch\FastCGI\Request');
        $request->getID()->willReturn(42);
        $request->getParameters()->willReturn(['some' => 'param']);
        $request->getStdin()->willReturn('foobar');

        $connection = new Connection($this->socket->reveal(), new BuilderFactory);

        $connection->sendRequest($request->reveal());
    }

    /**
     * @covers ::receiveResponse
     */
    public function testReceiveRequest()
    {
        $request = $this->prophesize('\Crunch\FastCGI\Request');
        $request->getID()->willReturn(42);
        $request->getParameters()->willReturn(['some' => 'param']);
        $request->getStdin()->willReturn('foobar');

        $this->socket->selectRead(Argument::any())->willReturn(true, false);
        $this->socket->recv(Argument::type('int'), Argument::type('int'))->willReturn("\x01\x03\x00\x2A\x00\x00\x00\x00");
        $this->socket->close()->willReturn(null);


        $builder = $this->prophesize('\Crunch\FastCGI\ResponseBuilder');
        $builder->isComplete()->willReturn(false, true);
        $builder->addRecord(Argument::type('\Crunch\FastCGI\Record'))->shouldBeCalled();
        $builder->build()->willReturn($this->prophesize('\Crunch\FastCGI\Response')->reveal());

        $socket = $this->socket->reveal();
        $connection = new Connection($socket, new BuilderFactory);
        $reflection = new \ReflectionClass($connection);
        $property = $reflection->getProperty('builder');
        $property->setAccessible(true);
        $property->setValue($connection, [42 => $builder->reveal()]);

        $connection->receiveResponse($request->reveal());
    }
}
