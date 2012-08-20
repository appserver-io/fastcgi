<?php
namespace Crunch\FastCGI;

class Connection {
    protected $_socket;
    protected $_nextId = 1;
    protected $_builder = array();
    const RESPONDER = 1;
    const AUTHORIZER = 2;
    const FILTER = 3;
    public function __construct ($socket) {
        $this->_socket = $socket;
        stream_set_blocking($this->_socket, 0);
    }
    public function __destruct() {
        fclose($this->_socket);
    }
    public function newRequest (array $params = null, $stdin = null) {
        return new Request($this->_nextId++, $params, $stdin);
    }
    public function request (Request $request) {
        $this->sendRequest($request);
        return $this->receiveResponse($request, 2);
    }
    public function sendRequest (Request $request) {
        $this->_builder[$request->ID] = new ResponseBuilder;
        $this->_builder[$request->ID] = new ResponseBuilder;
        $this->sendRecord(new Record(Record::BEGIN_REQUEST, $request->ID, pack('xCCxxxxx', self::RESPONDER, 0xFF & 1)));

        $p = '';
        foreach ($request->parameters as $name => $value) {
            $p .= pack('NN', strlen($name) + 0x80000000, strlen($value) + 0x80000000) . $name . $value;
        }
        $this->sendRecord(new Record(Record::PARAMS, $request->ID, $p));
        $this->sendRecord(new Record(Record::PARAMS, $request->ID, ''));

        $this->sendRecord(new Record(Record::STDIN, $request->ID, $request->stdin));
        $this->sendRecord(new Record(Record::STDIN, $request->ID, ''));
    }
    public function sendRecord (Record $record) {
        \fwrite($this->_socket, (string) $record, count($record));
    }

    public function receiveResponse (Request $request) {
        while (!$this->_builder[$request->ID]->isComplete) {
            $this->receiveAll(2);
        }
        return $this->_builder[$request->ID]->buildResponse();
    }

    public function receiveAll ($timeout) {
        while ($record = $this->receiveRecord($timeout)) {
            $this->_builder[$record->requestId]->addRecord($record);
            $timeout = 0; // Reset timeout to avoid stuttering on subsequent requests
        }
    }
    private $_buffer = array();
    public function receiveRecord ($timeout) {
        $read = array($this->_socket);
        $write = $except = array();
        // If we already have some records fetched, we don't need to wait for another one, thus we should look
        // if there is something and keep going without waiting
        if (\stream_select($read, $write, $except, $this->_buffer ? 0 : $timeout)) {
            while ($header = \fread($read[0], 8 /* header length */)) {
                $header = unpack('Cversion/Ctype/nrequestId/ncontentLength/CpaddingLength/Creserved', $header);
                $content = \fread($this->_socket, $header['contentLength']);
                $this->_buffer[] = new Record($header['type'], $header['requestId'], $content);
                \fseek($this->_socket, $header['paddingLength'], \SEEK_CUR);
            }
        }
        return \array_shift($this->_buffer);
    }
}
