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
    private $connectionProphet;
    /** @var ObjectProphecy */
    private $clientFactoryProphet;

    protected function setUp()
    {
        parent::setUp();

        $this->connectionProphet = $this->prophesize('\Crunch\FastCGI\Connection');
        $this->clientFactoryProphet = $this->prophesize('\Crunch\FastCGI\ClientFactory');
        $this->clientFactoryProphet
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

        $request = $client->newRequest(new RequestParameters(['some' => 'param']), 'foobar');

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

        $request = $client->newRequest(new RequestParameters(['some' => 'param']), 'foobar');

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

        $parameters = new RequestParameters(['some' => 'param']);
        $request = $client->newRequest($parameters, 'foobar');

        self::assertSame($parameters, $request->getParameters());
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Request::__construct
     * @uses \Crunch\FastCGI\Request::getStdin
     */
    public function testNewInstanceKeepsBody()
    {
        $client = new Client($this->connectionProphet->reveal());

        $request = $client->newRequest(new RequestParameters(['some' => 'param']), 'foobar');

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
        $requestProphet = $this->prophesize('\Crunch\FastCGI\Request');
        $requestProphet->getID()->willReturn(42);
        $responseBuilderProphet = $this->prophesize('\Crunch\FastCGI\ResponseBuilder');
        $responseBuilderProphet->isComplete()->willReturn(true);
        $responseProphet = $this->prophesize('\Crunch\FastCGI\ResponseInterface');
        $response = $responseProphet->reveal();
        $responseBuilderProphet->build()->willReturn($response);

        $client = new Client($this->connectionProphet->reveal());

        $refClient = new \ReflectionObject($client);
        $refProperty = $refClient->getProperty('responseBuilders');
        $refProperty->setAccessible(true);
        $refProperty->setValue($client, [42 => $responseBuilderProphet->reveal()]);

        $request = $requestProphet->reveal();

        self::assertSame($response, $client->receiveResponse($request));
    }


    /**
     * @covers ::receiveResponse
     */
    public function testExceptionWhenTheRequestWerentSentBefore()
    {
        $this->setExpectedException('\Crunch\FastCGI\ClientException');

        $requestProphet = $this->prophesize('\Crunch\FastCGI\Request');
        $requestProphet->getID()->willReturn(42);

        $request = $requestProphet->reveal();

        $client = new Client($this->connectionProphet->reveal());

        $client->receiveResponse($request);
    }

    /**
     * @covers ::receiveResponse
     */
    public function testExceptionWhenThereComesARecordForAnUnknownRequestId()
    {
        $this->setExpectedException('\Crunch\FastCGI\ClientException');

        $requestProphet = $this->prophesize('\Crunch\FastCGI\Request');
        $requestProphet->getID()->willReturn(42);
        $responseBuilderProphet = $this->prophesize('\Crunch\FastCGI\ResponseBuilder');
        $responseBuilderProphet->isComplete()->willReturn(false);
        $recordProphet = $this->prophesize('\Crunch\FastCGI\Record');
        $recordProphet->getRequestId()->willReturn(23);
        $this->connectionProphet
            ->receive(Argument::type('integer'))
            ->willReturn($recordProphet->reveal());


        $client = new Client($this->connectionProphet->reveal());

        $refClient = new \ReflectionObject($client);
        $refProperty = $refClient->getProperty('responseBuilders');
        $refProperty->setAccessible(true);
        $refProperty->setValue($client, [42 => $responseBuilderProphet->reveal()]);

        $request = $requestProphet->reveal();

        $client->receiveResponse($request);
    }


    /**
     * @covers ::receiveResponse
     */
    public function testReceiveResponseReturnsNullWhenResponseIsIncomplete()
    {
        $requestProphet = $this->prophesize('\Crunch\FastCGI\Request');
        $requestProphet->getID()->willReturn(42);
        $responseBuilderProphet = $this->prophesize('\Crunch\FastCGI\ResponseBuilder');
        $responseBuilderProphet->isComplete()->willReturn(false);
        $recordProphet = $this->prophesize('\Crunch\FastCGI\Record');
        $recordProphet->getRequestId()->willReturn(42);
        $record = $recordProphet->reveal();
        $responseBuilderProphet->addRecord($record)->shouldBeCalled();
        $this->connectionProphet
            ->receive(Argument::type('integer'))
            ->willReturn($record, null);


        $client = new Client($this->connectionProphet->reveal());

        $refClient = new \ReflectionObject($client);
        $refProperty = $refClient->getProperty('responseBuilders');
        $refProperty->setAccessible(true);
        $refProperty->setValue($client, [42 => $responseBuilderProphet->reveal()]);

        $request = $requestProphet->reveal();

        self::assertNull($client->receiveResponse($request));
    }
}
