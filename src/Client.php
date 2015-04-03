<?php
namespace Crunch\FastCGI;

class Client
{
    /** @var string */
    private $host;
    /** @var ConnectionFactory */
    private $connectionFactory;
    /** @var Connection */
    private $connection = null;

    private $nextId = 1;

    /** @var ResponseBuilder[] */
    private $responseBuilders = [];

    /**
     * Client constructor.
     * @param string $host
     * @param ConnectionFactory $connectionFactory
     */
    public function __construct($host, ConnectionFactory $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
        $this->host = $host;
    }

    public function connect()
    {
        if (!$this->connection) {
            $this->connection = $this->connectionFactory->connect($this->host);
        }
    }

    private function connection()
    {
        $this->connect();
        return $this->connection;
    }


    /**
     * Creates a new request
     *
     * @param string[]|null $params
     * @param string|null $stdin
     * @return Request
     */
    public function newRequest(array $params = null, $stdin = null)
    {
        return new Request($this->nextId++, $params, $stdin);
    }

    /**
     * Send request and awaits the response (sequential)
     *
     * @param Request $request
     * @return Response
     */
    public function request(Request $request)
    {
        $this->sendRequest($request);
        return $this->receiveResponse($request);
    }

    /**
     * Send request, but don't wait for response
     *
     * Remember to call receiveResponse(). Else, it will remain the buffer.
     *
     * @param Request $request
     */
    public function sendRequest(Request $request)
    {
        $this->responseBuilders[$request->getID()] = new ResponseBuilder;
        foreach (Record::buildFromRequest($request) as $record) {
            $this->connection()->send($record);
        }
    }

    /**
     * Receive response
     *
     * Returns the response a request previously sent with sendRequest()
     *
     * @param Request $request
     * @return Response
     */
    public function receiveResponse(Request $request)
    {
        if (!isset($this->responseBuilders[$request->getID()])) {
            throw new \Exception('Client never performed a request for request ID '. $request->getID());
        }

        while (!$this->responseBuilders[$request->getID()]->isComplete() && $record = $this->connection()->receive(10)) {
            if (!isset($this->responseBuilders[$record->getRequestId()])) {
                throw new \Exception('Received unexpected request ID ' . $record->getRequestId());
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
