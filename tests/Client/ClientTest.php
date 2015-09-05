<?php
namespace Crunch\FastCGI\Client;

use Crunch\FastCGI\Protocol\RequestParameters;
use Crunch\FastCGI\ReaderWriter\StringReader;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \Crunch\FastCGI\Client\Client
 * @covers \Crunch\FastCGI\Client\Client
 */
class ClientTest extends TestCase
{
    /** @var ObjectProphecy */
    private $connectionProphet;
    /** @var ObjectProphecy */
    private $connectionFactoryProphet;

    /** @var ObjectProphecy */
    private $requestParametersProphet;
    /** @var ObjectProphecy */
    private $readerProphet;

    protected function setUp()
    {
        TestCase::setUp();

        $this->connectionProphet = $this->prophesize('\Crunch\FastCGI\Connection\Connection');

        $this->connectionFactoryProphet = $this->prophesize('\Crunch\FastCGI\Connection\ConnectionFactoryInterface');
        $this->connectionFactoryProphet
            ->connect()
            ->willReturn($this->connectionProphet->reveal());

        $this->requestParametersProphet = $this->prophesize('\Crunch\FastCGI\Protocol\RequestParametersInterface');
        $this->readerProphet = $this->prophesize('\Crunch\FastCGI\ReaderWriter\ReaderInterface');
    }


    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Protocol\Request::__construct
     */
    public function testCreateNewRequest()
    {
        $client = new Client($this->connectionFactoryProphet->reveal());

        $request = $client->newRequest($this->requestParametersProphet->reveal(), $this->readerProphet->reveal());

        self::assertInstanceOf('\Crunch\FastCGI\Protocol\Request', $request);
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Protocol\Request::__construct
     * @uses \Crunch\FastCGI\Protocol\getRequestId::getRequestID
     */
    public function testNewInstanceHasIntegerId()
    {
        $client = new Client($this->connectionFactoryProphet->reveal());

        $request = $client->newRequest($this->requestParametersProphet->reveal(), $this->readerProphet->reveal());

        self::assertInternalType('integer', $request->getID());
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Protocol\Request::__construct
     * @uses \Crunch\FastCGI\Protocol\Request::getParameters
     */
    public function testNewInstanceKeepsParameters()
    {
        $client = new Client($this->connectionFactoryProphet->reveal());

        $parameters = $this->requestParametersProphet->reveal();
        $request = $client->newRequest($parameters, $this->readerProphet->reveal());

        self::assertSame($parameters, $request->getParameters());
    }

    /**
     * @covers ::newRequest
     * @uses \Crunch\FastCGI\Protocol\Request::__construct
     * @uses \Crunch\FastCGI\Protocol\Request::getStdin
     */
    public function testNewInstanceKeepsBody()
    {
        $client = new Client($this->connectionFactoryProphet->reveal());

        $this->readerProphet->read(Argument::type('integer'))->willReturn('foobar', '');
        $request = $client->newRequest($this->requestParametersProphet->reveal(), $this->readerProphet->reveal());

        self::assertEquals('foobar', $request->getStdin()->read(6));
    }

    /**
     * @covers ::sendRequest
     * @uses \Crunch\FastCGI\Protocol\Record
     * @uses \Crunch\FastCGI\Protocol\Header
     */
    public function testSendRequest()
    {
        $requestProphet = $this->prophesize('\Crunch\FastCGI\Protocol\Request');
        $recordProphet = $this->prophesize('\Crunch\FastCGI\Protocol\Record');

        $record = $recordProphet->reveal();
        $requestProphet->toRecords()->willReturn([$record]);
        $requestProphet->getID()->willReturn(42);

        $client = new Client($this->connectionFactoryProphet->reveal());

        $client->sendRequest($requestProphet->reveal());

        $this->connectionProphet->send($record)->shouldHaveBeenCalled();
    }

    /**
     * @covers ::receiveResponse
     */
    public function testReceiveRequest()
    {
        $requestProphet = $this->prophesize('\Crunch\FastCGI\Protocol\Request');
        $requestProphet->getID()->willReturn(42);
        $responseBuilderProphet = $this->prophesize('\Crunch\FastCGI\Client\ResponseBuilder');
        $responseBuilderProphet->isComplete()->willReturn(true);
        $responseProphet = $this->prophesize('\Crunch\FastCGI\Protocol\ResponseInterface');
        $response = $responseProphet->reveal();
        $responseBuilderProphet->build()->willReturn($response);

        $client = new Client($this->connectionFactoryProphet->reveal());

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
        $this->setExpectedException('\Crunch\FastCGI\Client\ClientException');

        $requestProphet = $this->prophesize('\Crunch\FastCGI\Protocol\Request');
        $requestProphet->getID()->willReturn(42);

        $request = $requestProphet->reveal();

        $client = new Client($this->connectionFactoryProphet->reveal());

        $client->receiveResponse($request);
    }

    /**
     * @covers ::receiveResponse
     */
    public function testExceptionWhenThereComesARecordForAnUnknownRequestId()
    {
        $this->setExpectedException('\Crunch\FastCGI\Client\ClientException');

        $requestProphet = $this->prophesize('\Crunch\FastCGI\Protocol\Request');
        $requestProphet->getID()->willReturn(42);
        $responseBuilderProphet = $this->prophesize('\Crunch\FastCGI\Client\ResponseBuilder');
        $responseBuilderProphet->isComplete()->willReturn(false);
        $recordProphet = $this->prophesize('\Crunch\FastCGI\Protocol\Record');
        $recordProphet->getRequestId()->willReturn(23);
        $this->connectionProphet
            ->receive(Argument::type('integer'))
            ->willReturn($recordProphet->reveal());


        $client = new Client($this->connectionFactoryProphet->reveal());

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
        $requestProphet = $this->prophesize('\Crunch\FastCGI\Protocol\Request');
        $requestProphet->getID()->willReturn(42);
        $responseBuilderProphet = $this->prophesize('\Crunch\FastCGI\Client\ResponseBuilder');
        $responseBuilderProphet->isComplete()->willReturn(false);
        $recordProphet = $this->prophesize('\Crunch\FastCGI\Protocol\Record');
        $recordProphet->getRequestId()->willReturn(42);
        $record = $recordProphet->reveal();
        $responseBuilderProphet->addRecord($record)->shouldBeCalled();
        $this->connectionProphet
            ->receive(Argument::type('integer'))
            ->willReturn($record, null);


        $client = new Client($this->connectionFactoryProphet->reveal());

        $refClient = new \ReflectionObject($client);
        $refProperty = $refClient->getProperty('responseBuilders');
        $refProperty->setAccessible(true);
        $refProperty->setValue($client, [42 => $responseBuilderProphet->reveal()]);

        $request = $requestProphet->reveal();

        self::assertNull($client->receiveResponse($request));
    }
}
