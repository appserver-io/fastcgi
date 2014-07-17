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
    protected $socket;

    /**
     * Next request ID to use
     *
     * @var int
     */
    protected $nextId = 1;

    /**
     * @var ResponseBuilder[]
     */
    protected $builder = array();

    /**
     * Internal record buffer
     *
     * To take pressure from the streams read-buffer.
     *
     * @var array
     */
    private $recordBuffer = array();

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
     * @param array|null  $params
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
        $this->builder[$request->ID] = new ResponseBuilder;
        $this->sendRecord(new Record(Record::BEGIN_REQUEST, $request->ID, \pack('xCCxxxxx', self::RESPONDER, 0xFF & 1)));

        $p = '';
        foreach ($request->parameters as $name => $value) {
            $p .= \pack('NN', \strlen($name) + 0x80000000, \strlen($value) + 0x80000000) . $name . $value;
        }
        $this->sendRecord(new Record(Record::PARAMS, $request->ID, $p));
        $this->sendRecord(new Record(Record::PARAMS, $request->ID, ''));

        // Unify input
        $stream = is_string($request->stdin)
            ? fopen('data://text/plain;base64,' . base64_encode($request->stdin), 'rb')
            : $request->stdin;
        while ($chunk = fread($stream, 65535)) {
            $this->sendRecord(new Record(Record::STDIN, $request->ID, $chunk));
        }
        $this->sendRecord(new Record(Record::STDIN, $request->ID, ''));
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
        while (!$this->builder[$request->ID]->isComplete) {
            $this->receiveAll(2);
        }

        return $this->builder[$request->ID]->buildResponse();
    }

    /**
     * Send a single record
     *
     * @param Record $record
     */
    protected function sendRecord (Record $record)
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
    protected function receiveAll ($timeout)
    {
        while ($record = $this->receiveRecord($timeout)) {
            $this->builder[$record->requestId]->addRecord($record);
            $timeout = 0; // Reset timeout to avoid stuttering on subsequent requests
        }
    }

    /**
     * Tries to receive a new record from the streams read-buffer
     *
     * @param int $timeout
     * @return Record
     */
    protected function receiveRecord ($timeout)
    {
        if (feof($this->socket)) {
            throw new ConnectionException('Connection to FastCGI server went away');
        }

        $read = array($this->socket);
        $write = $except = array();
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
