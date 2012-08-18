<?php
namespace Crunch\FastCGI;

class Record implements \Countable {
    const BEGIN_REQUEST = 1;
    const ABORT_REQUEST = 2;
    const END_REQUEST = 3;
    const PARAMS = 4;
    const STDIN = 5;
    const STDOUT = 6;
    const STDERR = 7;
    const DATA = 8;
    const GET_VALUES = 9;
    const GET_VALUES_RESULT = 10;
    const UNKNOWN_TYPE = 11;
    const MAXTYPE = self::UNKNOWN_TYPE;

    public $version = 1;
    public $type;
    public $requestId;
    public $content;
    public function __construct ($type, $requestId, $content) {
        $this->type = $type;
        $this->requestId = $requestId;
        $this->content = $content;
    }

    public function pack () {
        $oversize = strlen((string) $this->content) % 8;
        $pack = pack('CCnnCx', 1, $this->type, $this->requestId, strlen((string) $this->content), $oversize ? 8 - $oversize : 0)
            . ((string) $this->content) . str_repeat("\0", $oversize ? 8 - $oversize : 0);
        var_dump($pack);
        return $pack;
    }

    public function __toString () {
        return $this->pack();
    }
    public function count () {
        return strlen($this->pack());
    }

    public function isSendable () {
        return in_array($this->type, array(self::BEGIN_REQUEST, self::ABORT_REQUEST, self::PARAMS, self::STDIN, self::DATA, self::GET_VALUES));
    }
}
