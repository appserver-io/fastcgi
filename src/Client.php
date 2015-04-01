<?php
namespace Crunch\FastCGI;

class Client
{
    /** @var ClientRecordHandler */
    private $handler;
    /** @var Connection */
    private $connection;

    private $nextId = 1;

    /**
     * Client constructor.
     * @param ClientRecordHandler $handler
     * @param Connection $connection
     */
    public function __construct(ClientRecordHandler $handler, Connection $connection)
    {
        $this->handler = $handler;
        $this->connection = $connection;
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
        $this->handler->expectResponse($request->getID());
        foreach (Record::buildFromRequest($request) as $record) {
            $this->connection->send($record);
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
        for ($i = 0; !$this->handler->isComplete($request->getID()); $i++) {
            $this->connection->receive(10);
        }

        return $this->handler->createResponse($request->getID());
    }
}
