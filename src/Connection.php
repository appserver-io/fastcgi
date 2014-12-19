<?php
namespace Crunch\FastCGI;

class Connection
{
    const RESPONDER = 1;
    const AUTHORIZER = 2;
    const FILTER = 3;

    /**
     * Hostname
     *
     * May be either a host name, or a (local) path to the FCGI-socket
     *
     * - localhost
     * - unix:///var/run/php5-fpm.sock
     *
     * @var string
     */
    protected $host;

    /**
     * Port number
     *
     * Required for net-based connections, ignored for socket connections
     *
     * @var int|null
     */
    protected $port;

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
     * @param string   $host
     * @param string   $port
     */
    public function __construct ($socket, $host, $port)
    {
        $this->socket = $socket;
        $this->host = $host;
        $this->port = $port;
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
     * @throws \Crunch\FastCGI\ConnectionException
     */
    protected function receiveRecord ($timeout)
    {
        $read = array($this->socket);
        $write = $except = array();
        // If we already have some records fetched, we don't need to wait for another one, thus we should look
        // if there is something and keep going without waiting

        if ($streamSelect = \stream_select($read, $write, $except, $this->recordBuffer ? 0 : $timeout)) {

            $header = \fread($read[0], 8 /* header length */);

            if (is_string($header) && strlen($header) > 0) {

                do {

                    $header = \unpack('Cversion/Ctype/nrequestId/ncontentLength/CpaddingLength/Creserved', $header);
                    $content = '';
                    do {
                        $content .= \stream_get_contents($this->socket, $header['contentLength'] - \strlen($content));
                        // break the loop if the content length has reached
                        if (strlen($content) >= $header['contentLength']) {
                            break;
                        }

                    } while (is_resource($this->socket));
                    $this->recordBuffer[] = new Record($header['type'], $header['requestId'], $content);
                    \fseek($this->socket, $header['paddingLength'], \SEEK_CUR);

                } while (strlen($header = \fread($read[0], 8 /* header length */)) > 0);

            } else {
                // check if backend is still up
                if (!is_resource($testConnection = @fsockopen($this->host, $this->port))) {
                    // if not throw exception
                    throw new ConnectionException(
                        'Connection has gone away during processing of request ID ' . ($this->nextId - 1)
                    );
                } else {
                    // close test connection
                    fclose($testConnection);
                }

            }
        }
        return \array_shift($this->recordBuffer);
    }
}
