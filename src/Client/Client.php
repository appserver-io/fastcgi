<?php
namespace Crunch\FastCGI\Client;

use Crunch\FastCGI\Connection\Connection;
use Crunch\FastCGI\Connection\ConnectionFactoryInterface;
use Crunch\FastCGI\Protocol\Header;
use Crunch\FastCGI\Protocol\Record;
use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\RequestInterface;
use Crunch\FastCGI\Protocol\RequestParametersInterface;
use Crunch\FastCGI\Protocol\ResponseInterface;
use Crunch\FastCGI\ReaderWriter\ReaderInterface;
use React\Promise\Deferred;
use React\SocketClient\ConnectorInterface;
use React\Stream\DuplexStreamInterface;
use React\Stream\Stream;

class Client
{
    /** @var Connection|null */
    private $connector;
    /** @var int Next request id to use */
    private $nextRequestId = 1;
    /** @var ResponseBuilder[] */
    private $responseBuilders = [];

    /** @var Deferred[] */
    private $promises = [];

    /**
     * Creates new client instance
     *
     * @param ConnectionFactoryInterface $connectionFactory
     */
    public function __construct(DuplexStreamInterface $connector)
    {
        $connector->on('data', function($data) {
            $this->read($data);
        });
        $this->connector = $connector;
    }

    /**
     * Creates a new request
     *
     * Although you can create a Request instance manually it is highly
     * recommended to use this factory method, because only this one
     * ensures, that the request uses a previously unused request id.
     *
     * @param RequestParametersInterface|null $parameters
     * @param ReaderInterface|null $stdin
     * @return RequestInterface
     */
    public function newRequest(RequestParametersInterface $parameters = null, ReaderInterface $stdin = null)
    {
        return new Request($this->nextRequestId++, $parameters, $stdin);
    }

    /**
     * Send request, but don't wait for response
     *
     * Remember to call receiveResponse(). Else, it will remain the buffer.
     *
     * @param RequestInterface $request
     */
    public function sendRequest(RequestInterface $request)
    {
        $this->responseBuilders[$request->getID()] = new ResponseBuilder;
        $this->promises[$request->getID()] = new Deferred();
        foreach ($request->toRecords() as $record) {
            $this->connector->write($record->encode());
        }

        return $this->promises[$request->getID()]->promise();
    }

    private $data = '';

    private function read($data)
    {
        $this->data .= $data;

        while ($this->data) {
            $header = Header::decode(substr($this->data, 0, 8));

            if (strlen($this->data) < $header->getPayloadLength() + 8) {
                return;
            }

            $rawRecord = substr($this->data, 8, $header->getLength());
            $record = Record::decode($header, $rawRecord);
            $this->data = substr($this->data, 8 + $header->getPayloadLength());

            $this->responseBuilders[$header->getRequestId()]->addRecord($record);
            if ($this->responseBuilders[$header->getRequestId()]->isComplete()) {
                $this->promises[$header->getRequestId()]->resolve($this->responseBuilders[$header->getRequestId()]->build());
            }
        }
    }

    public function close()
    {
        $this->connector->close();
    }
}
