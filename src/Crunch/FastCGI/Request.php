<?php
namespace Crunch\FastCGI;

class Request {
    public $parameters;
    public $stdin;
    public $ID;
    public function __construct ($requestId, array $params = null, $stdin = null) {
        $this->ID = $requestId;
        $this->parameters = $params ?: array();
        $this->stdin = $stdin ?: '';
    }
}
