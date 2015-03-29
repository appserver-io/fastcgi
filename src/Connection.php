<?php
namespace Crunch\FastCGI;

use Socket\Raw\Socket;

class Connection
{
    const RESPONDER = 1;
    const AUTHORIZER = 2;
    const FILTER = 3;

    /** @var Socket Stream socket to FastCGI */
    private $socket;
    /** @var BuilderFactory */
    private $builderFactory;

    /** @var int Next request id */
    private $nextId = 1;
    /** @var ResponseBuilder[] */
    private $builder = [];

    /**
     * @param Socket $socket
     * @param BuilderFactory $factory
     */
    public function __construct(Socket $socket, BuilderFactory $factory)
    {
        $this->socket = $socket;
        $this->builderFactory = $factory;
    }

    /**
     * Close socket
     */
    public function __destruct()
    {
        $this->socket->close();
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
        return $this->receiveResponse($request, 2);
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
        foreach (Record::buildFromRequest($request) as $record) {
            $this->sendRecord($record);
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
        // At some point we want to have different behaviour between "There is no record at all"
        // and "it's simply slow"
        for ($i = 0; !isset($this->builder[$request->getID()]); $i++) {
            if ($i > 10) {
                throw new \RuntimeException("Timeout");
            }
            $this->receiveAll(10);
        }
        for ($i = 0; !$this->builder[$request->getID()]->isComplete(); $i++) {
            if ($i > 10) {
                throw new \RuntimeException("Timeout");
            }
            $this->receiveAll(10);
        }

        return $this->builder[$request->getID()]->build();
    }


    /**
     * Send a single record
     *
     * @param Record $record
     * @throws \Exception
     */
    private function sendRecord(Record $record)
    {
        if (!$this->socket->selectWrite(4)) {
            throw new \Exception('Socket not ready exception');
        }
        $this->socket->send($record->pack(), 0);
    }

    private function receiveAll($timeout)
    {
        while ($this->socket->selectRead($timeout) && $header = $this->socket->recv(8, \MSG_WAITALL)) {
            $header = Header::decode($header);

            $packet = $this->socket->recv($header->getPayloadLength(), \MSG_WAITALL);
            $record = Record::unpack($header, $packet);

            if (!isset($this->builder[$record->getRequestId()])) {
                $this->builder[$record->getRequestId()] = $this->builderFactory->create($record);
            }
            $this->builder[$record->getRequestId()]->addRecord($record);

            $timeout = 0; // Reset timeout to avoid stuttering on subsequent requests
        }
    }
}
