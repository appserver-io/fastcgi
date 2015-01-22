<?php
namespace Crunch\FastCGI;

use Socket\Raw\Socket;

class Connection
{
    const RESPONDER = 1;
    const AUTHORIZER = 2;
    const FILTER = 3;

    /**
     * Stream socket to FastCGI
     *
     * @var Socket
     */
    private $socket;

    /**
     * Next request ID to use
     *
     * @var int
     */
    private $nextId = 1;

    /**
     * @var ResponseBuilder[]
     */
    private $builder = [];

    /**
     * Internal record buffer
     *
     * To take pressure from the streams read-buffer.
     *
     * @var Record[]
     */
    private $recordBuffer = [];

    /**
     * @param Socket $socket
     */
    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
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
        $this->builder[$request->getID()] = new ResponseBuilder;
        $this->sendRecord(new Record(Record::BEGIN_REQUEST, $request->getID(), \pack('xCCxxxxx', self::RESPONDER, 0xFF & 1)));

        $packet = '';
        foreach ($request->getParameters() as $name => $value) {
            $new = \pack('NN', \strlen($name) + 0x80000000, \strlen($value) + 0x80000000) . $name . $value;
            if (strlen($new) + strlen($packet) > 65535) {
                $this->sendRecord(new Record(Record::PARAMS, $request->getID(), $packet));
                $packet = '';
            }
            $packet .= $new;
        }
        $this->sendRecord(new Record(Record::PARAMS, $request->getID(), $packet));
        $this->sendRecord(new Record(Record::PARAMS, $request->getID(), ''));

        // Unify input
        $stream = is_string($request->getStdin())
            ? fopen('data://text/plain;base64,' . base64_encode($request->getStdin()), 'rb')
            : $request->getStdin();
        while ($chunk = fread($stream, 65535)) {
            $this->sendRecord(new Record(Record::STDIN, $request->getID(), $chunk));
        }
        $this->sendRecord(new Record(Record::STDIN, $request->getID(), ''));
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
        while (!$this->builder[$request->getID()]->isComplete()) {
            $this->receiveAll(2);
        }

        return $this->builder[$request->getID()]->buildResponse();
    }


    /**
     * Send a single record
     *
     * @param Record $record
     */
    private function sendRecord(Record $record)
    {
        $this->socket->send($record->pack(), 0);
    }


    /**
     * Receive all currently available records
     *
     * This will read as may records as possible, which does _not_ mean, that
     * it will read every record, that _should_ appear.
     *
     * @param int $timeout
     */
    private function receiveAll($timeout)
    {
        $this->fetchRecords($timeout);
        while ($record = \array_shift($this->recordBuffer)) {
            $this->builder[$record->getRequestId()]->addRecord($record);
        }
    }

    private function fetchRecords($timeout)
    {
        while ($this->socket->selectRead($timeout) && $header = $this->socket->recv(8, \MSG_WAITALL | \MSG_PEEK)) {
            $length = \array_sum(\unpack('nlength/Cpadding', substr($header, 4, 3)));

            $packet = $this->socket->recv($length + 8, \MSG_WAITALL);
            $this->recordBuffer[] = Record::unpack($packet);

            $timeout = 0; // Reset timeout to avoid stuttering on subsequent requests
        }
    }
}
