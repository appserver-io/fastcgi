<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;
use Socket\Raw\Socket;
use Phake_IMock as Mock;
use Phake;

/**
 * @coversDefaultClass \Crunch\FastCGI\Connection
 * @covers \Crunch\FastCGI\Connection
 */
class ConnectionTest extends TestCase
{
    /**
     * @var Socket|Mock
     */
    private $socket;

    protected function setUp()
    {
        parent::setUp();

        $this->socket = Phake::mock('\Socket\Raw\Socket');
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     */
    public function testCreateNewRequest()
    {
        $connection = new Connection($this->socket);

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
        $connection = new Connection($this->socket);

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
        $connection = new Connection($this->socket);

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
        $connection = new Connection($this->socket);

        $request = $connection->newRequest(['some' => 'param'], 'foobar');

        $this->assertEquals('foobar', $request->getStdin());
    }

    /**
     * @covers ::sendRequest
     * @uses \Crunch\FastCGI\Record
     */
    public function testSendRequest()
    {
        $request = Phake::mock('\Crunch\FastCGI\Request');
        Phake::when($request)->getID()->thenReturn(42);
        Phake::when($request)->getParameters()->thenReturn(['some' => 'param']);
        Phake::when($request)->getStdin()->thenReturn('foobar');

        $connection = new Connection($this->socket);

        $connection->sendRequest($request);

        Phake::verify($this->socket, Phake::atLeast(1))->send(Phake::anyParameters());
    }

    /**
     * @covers ::receiveResponse
     */
    public function testReceiveRequest()
    {
        $request = Phake::mock('\Crunch\FastCGI\Request');
        Phake::when($request)->getID()->thenReturn(42);
        Phake::when($request)->getParameters()->thenReturn(['some' => 'param']);
        Phake::when($request)->getStdin()->thenReturn('foobar');

        $builder = Phake::mock('\Crunch\FastCGI\ResponseBuilder');
        Phake::when($builder)->isComplete()->thenReturn(false)->thenReturn(true);

        Phake::when($this->socket)->selectRead(Phake::anyParameters())->thenReturn(true)->thenReturn(false);
        Phake::when($this->socket)->recv(8, \MSG_WAITALL)->thenReturn("\x01\x03\x00\x2A\x00\x00\x00\x00");

        $connection = new Connection($this->socket);
        $reflection = new \ReflectionClass($connection);
        $property = $reflection->getProperty('builder');
        $property->setAccessible(true);
        $property->setValue($connection, [42 => $builder]);

        $connection->receiveResponse($request);

        Phake::verify($this->socket, Phake::atLeast(1))->recv(Phake::anyParameters());
    }
}
