<?php
namespace Crunch\FastCGI;

class Client {
    protected $_host;
    protected $_port;

    public function __construct($host, $port = null) {
        $this->_host = $host;
        $this->_port = $port;
    }
    public function connect () {
        if ($socket = fsockopen($this->_host, $this->_port, $errorCode, $error, 20)) {
            return new Connection($socket);
        }

        throw new \RuntimeException('Could not establish conntent: ' . $error, $errorCode);
    }
}
