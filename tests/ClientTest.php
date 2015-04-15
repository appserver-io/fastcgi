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
    private $recordHandlerProphet;
    /** @var ObjectProphecy */
    private $connectionProphet;
    /** @var ObjectProphecy */
    private $connectionFactoryProphet;

    protected function setUp()
    {
        parent::setUp();

        $this->connectionProphet = $this->prophesize('\Crunch\FastCGI\Connection');
        $this->connectionFactoryProphet = $this->prophesize('\Crunch\FastCGI\ConnectionFactory');
        $this->connectionFactoryProphet
            ->connect(Argument::type('string'), Argument::any())
            ->willReturn($this->connectionProphet->reveal());
    }


    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     */
    public function testCreateNewRequest()
    {
        $client = new Client($this->connectionProphet->reveal());

        $request = $client->newRequest(['some' => 'param'], 'foobar');

        self::assertInstanceOf('\Crunch\FastCGI\Request', $request);
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     * @uses \Crunch\FastCGI\Request::getID
     */
    public function testNewInstanceHasIntegerId()
    {
        $client = new Client($this->connectionProphet->reveal());

        $request = $client->newRequest(['some' => 'param'], 'foobar');

        self::assertInternalType('integer', $request->getID());
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     * @uses \Crunch\FastCGI\Request::getParameters
     */
    public function testNewInstanceKeepsParameters()
    {
        $client = new Client($this->connectionProphet->reveal());

        $request = $client->newRequest(['some' => 'param'], 'foobar');

        self::assertEquals(['some' => 'param'], $request->getParameters());
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     * @uses \Crunch\FastCGI\Request::getStdin
     */
    public function testNewInstanceKeepsBody()
    {
        $client = new Client($this->connectionProphet->reveal());

        $request = $client->newRequest(['some' => 'param'], 'foobar');

        self::assertEquals('foobar', $request->getStdin());
    }

    /**
     * @covers ::sendRequest
     * @uses \Crunch\FastCGI\Record
     * @uses \Crunch\FastCGI\Header
     */
    public function testSendRequest()
    {
        $requestProphet = $this->prophesize('\Crunch\FastCGI\Request');
        $recordProphet = $this->prophesize('\Crunch\FastCGI\Record');

        $record = $recordProphet->reveal();
        $requestProphet->toRecords()->willReturn([$record]);
        $requestProphet->getID()->willReturn(42);

        $client = new Client($this->connectionProphet->reveal());

        $client->sendRequest($requestProphet->reveal());

        $this->connectionProphet->send($record)->shouldHaveBeenCalled();
    }

    /**
     * @covers ::receiveResponse
     */
    public function testReceiveRequest()
    {
        self::markTestIncomplete('Doesn\'t work in current setup');


        $request = $this->prophesize('\Crunch\FastCGI\Request');
        $request->getID()->willReturn(42);

        $client = new Client($this->connectionProphet->reveal());

        $request = $request->reveal();
        $client->sendRequest($request);
        $client->receiveResponse($request);

        $this->recordHandlerProphet->createResponse(42)->shouldHaveBeenCalled();
    }
}
