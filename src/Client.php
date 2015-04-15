<?php
namespace Crunch\FastCGI;

class Client implements ClientInterface
{
    /** @var Connection */
    private $connection;
    /** @var int Next request id to use */
    private $nextRequestId = 1;
    /** @var ResponseBuilder[] */
    private $responseBuilders = [];

    /**
     * Creates new client instance
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }


    /**
     * Creates a new request
     *
     * Although you can create a Request instance manually it is highly
     * recommended to use this factory method, because only this one
     * ensures, that the request uses a previously unused request id.
     *
     * @param string[]|null $params
     * @param string|null $stdin
     * @return RequestInterface
     */
    public function newRequest(array $params = null, $stdin = null)
    {
        return new Request($this->nextRequestId++, $params, $stdin);
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
        foreach ($request->toRecords() as $record) {
            $this->connection->send($record);
        }
    }

    /**
     * Receive response
     *
     * Returns the response a request previously sent with sendRequest()
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function receiveResponse(RequestInterface $request)
    {
        if (!isset($this->responseBuilders[$request->getID()])) {
            throw new ClientException('Client never performed a request for request ID '. $request->getID());
        }

        while (!$this->responseBuilders[$request->getID()]->isComplete() && $record = $this->connection->receive(10)) {
            if (!isset($this->responseBuilders[$record->getRequestId()])) {
                throw new ClientException('Received unexpected request ID ' . $record->getRequestId());
            }

            $this->responseBuilders[$record->getRequestId()]->addRecord($record);
        }

        if (!$this->responseBuilders[$request->getID()]->isComplete()) {
            return null;
        }


        $response = $this->responseBuilders[$request->getID()]->build();
        unset($this->responseBuilders[$request->getID()]);

        return $response;
    }
}
