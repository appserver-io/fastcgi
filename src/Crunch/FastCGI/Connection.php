<?php
namespace Crunch\FastCGI;

class Connection {
    protected $_socket;
    protected $_nextId = 0;
    const RESPONDER = 1;
    const AUTHORIZER = 2;
    const FILTER = 3;
    public function __construct ($socket) {
        $this->_socket = $socket;
    }
    public function sendRequest (Request $request) {
        $requestId = $this->_nextId++;
        $this->sendRecord(new Record(Record::BEGIN_REQUEST, $requestId, pack('xCCxxxxx', self::RESPONDER, 0xFF & 1)));

        $p = '';
        foreach ($request->parameters as $name => $value) {
            $p .= pack('NN', strlen($name) + 0x80000000, strlen($value) + 0x80000000) . $name . $value;
        }
        $this->sendRecord(new Record(Record::PARAMS, $requestId, $p));
        $this->sendRecord(new Record(Record::PARAMS, $requestId, ''));

        $this->sendRecord(new Record(Record::STDIN, $requestId, $request->stdin));
        $this->sendRecord(new Record(Record::STDIN, $requestId, ''));

        $response = new Response;
        do {
            $record = $this->receiveRecord();
            switch ($record->type) {
                case Record::STDOUT:
                    $response->content .= $record->content;
                    break;
                case Record::STDERR:
                    $response->error .= $record->content;
                    break;
            }
        } while ($record && $record->type != Record::END_REQUEST);
        return $response;
    }
    public function sendRecord (Record $record) {
        \fwrite($this->_socket, (string) $record, count($record));
    }
    public function receiveRecord () {
        if (!($header = \fread($this->_socket, 8 /* header length */))) {
            throw new \RuntimeException('Failed reading header');
        }
        $resp = unpack('Cversion/Ctype/nrequestId/ncontentLength/CpaddingLength/Creserved', $header);
        $content = \fread($this->_socket, $resp['contentLength']);
        \fseek($this->_socket, $resp['paddingLength'], \SEEK_CUR);

        return new Record($resp['type'], $resp['requestId'], $content);
    }
}
