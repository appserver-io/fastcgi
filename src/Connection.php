<?php
namespace Crunch\FastCGI;

class Connection
{
    const RESPONDER = 1;
    const AUTHORIZER = 2;
    const FILTER = 3;

    /**
     * Stream socket to FastCGI
     *
     * @var resource
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
     * @var Record
     */
    private $recordBuffer = [];

    /**
     * @param resource $socket
     */
    public function __construct ($socket)
    {
        $this->socket = $socket;
        \stream_set_blocking($this->socket, 0);
    }

    /**
     * Close socket
     */
    public function __destruct()
    {
        \fclose($this->socket);
    }

    /**
     * Creates a new request
     *
     * @param string[]|null  $params
     * @param string|null $stdin
     * @return Request
     */
    public function newRequest (array $params = null, $stdin = null)
    {
        return new Request($this->nextId++, $params, $stdin);
    }

    /**
     * Send request and awaits the response (sequential)
     *
     * @param Request $request
     * @return Response
     */
    public function request (Request $request)
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
    public function sendRequest (Request $request)
    {
        $this->builder[$request->getID()] = new ResponseBuilder;
        $this->sendRecord(new Record(Record::BEGIN_REQUEST, $request->getID(), \pack('xCCxxxxx', self::RESPONDER, 0xFF & 1)));

        $p = '';
        foreach ($request->getParameters() as $name => $value) {
            $p .= \pack('NN', \strlen($name) + 0x80000000, \strlen($value) + 0x80000000) . $name . $value;
        }
        $this->sendRecord(new Record(Record::PARAMS, $request->getID(), $p));
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
    public function receiveResponse (Request $request)
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
    private function sendRecord (Record $record)
    {
        \fwrite($this->socket, (string) $record, \count($record));
    }

    /**
     * Receive all currently available records
     *
     * This will read as may records as possible, which does _not_ mean, that
     * it will read every record, that _should_ appear.
     *
     * @param int $timeout
     */
    private function receiveAll ($timeout)
    {
        while ($record = $this->receiveRecord($timeout)) {
            $this->builder[$record->getRequestId()]->addRecord($record);
            $timeout = 0; // Reset timeout to avoid stuttering on subsequent requests
        }
    }

    /**
     * Tries to receive a new record from the streams read-buffer
     *
     * @param int $timeout
     * @return Record
     */
    private function receiveRecord ($timeout)
    {
        if (feof($this->socket)) {
            throw new ConnectionException('Connection to FastCGI server went away');
        }

        $read = [$this->socket];
        $write = $except = [];
        // If we already have some records fetched, we don't need to wait for another one, thus we should look
        // if there is something and keep going without
        if (\stream_select($read, $write, $except, $this->recordBuffer ? 0 : $timeout)) {
            while ($header = \stream_get_contents($read[0], 8 /* header length */)) {
                $header = \unpack('Cversion/Ctype/nrequestId/ncontentLength/CpaddingLength/Creserved', $header);
                $content = $this->readBody($header['contentLength']);
                $this->recordBuffer[] = new Record($header['type'], $header['requestId'], $content);
                \fseek($this->socket, $header['paddingLength'], \SEEK_CUR);
            }
        }
        return \array_shift($this->recordBuffer);
    }

    private function readBody ($length)
    {
        $content = '';
        do {
            if (feof($this->socket)) {
                throw new ConnectionException('Connection to FastCGI server went away');
            }

            $content .= \stream_get_contents($this->socket, $length - \strlen($content));
        } while (\strlen($content) < $length);
        return $content;
    }
}
