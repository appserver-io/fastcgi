<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \Crunch\FastCGI\Client
 * @covers \Crunch\FastCGI\Client
 */
class ClientTest extends TestCase
{
    /** @var ObjectProphecy */
    private $recordHandler;
    /** @var ObjectProphecy */
    private $connectionFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->recordHandler = $this->prophesize('\Crunch\FastCGI\ClientRecordHandler');
        $this->connectionFactory = $this->prophesize('\Crunch\FastCGI\Connection');
    }


    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     */
    public function testCreateNewRequest()
    {
        $client = new Client($this->recordHandler->reveal(), $this->connectionFactory->reveal());

        $request = $client->newRequest(['some' => 'param'], 'foobar');

        $this->assertInstanceOf('\Crunch\FastCGI\Request', $request);
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     * @uses \Crunch\FastCGI\Request::getID
     */
    public function testNewInstanceHasIntegerId()
    {
        $client = new Client($this->recordHandler->reveal(), $this->connectionFactory->reveal());

        $request = $client->newRequest(['some' => 'param'], 'foobar');

        $this->assertInternalType('integer', $request->getID());
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     * @uses \Crunch\FastCGI\Request::getParameters
     */
    public function testNewInstanceKeepsParameters()
    {
        $client = new Client($this->recordHandler->reveal(), $this->connectionFactory->reveal());

        $request = $client->newRequest(['some' => 'param'], 'foobar');

        $this->assertEquals(['some' => 'param'], $request->getParameters());
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     * @uses \Crunch\FastCGI\Request::getStdin
     */
    public function testNewInstanceKeepsBody()
    {
        $client = new Client($this->recordHandler->reveal(), $this->connectionFactory->reveal());

        $request = $client->newRequest(['some' => 'param'], 'foobar');

        $this->assertEquals('foobar', $request->getStdin());
    }

    /**
     * @covers ::sendRequest
     * @uses \Crunch\FastCGI\Record
     * @uses \Crunch\FastCGI\Header
     */
    public function testSendRequest()
    {
        $request = $this->prophesize('\Crunch\FastCGI\Request');
        $request->getID()->willReturn(42);
        $request->getParameters()->willReturn(['some' => 'param']);
        $request->getStdin()->willReturn('foobar');

        $client = new Client($this->recordHandler->reveal(), $this->connectionFactory->reveal());

        $client->sendRequest($request->reveal());

        $this->recordHandler->expectResponse(42)->shouldHaveBeenCalled();
    }

    /**
     * @covers ::receiveResponse
     */
    public function testReceiveRequest()
    {
        $request = $this->prophesize('\Crunch\FastCGI\Request');
        $request->getID()->willReturn(42);

        $this->recordHandler->isComplete(42)->willReturn(true);
        $this->recordHandler->createResponse(42)->willReturn(
            $this->prophesize('\Crunch\FastCGI\Response')->reveal()
        );
        $client = new Client($this->recordHandler->reveal(), $this->connectionFactory->reveal());

        $client->receiveResponse($request->reveal());

        $this->recordHandler->createResponse(42)->shouldHaveBeenCalled();
    }
}
