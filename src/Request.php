<?php
namespace Crunch\FastCGI;

class Request
{
    /**
     * Request ID
     *
     * @var int
     */
    public $ID;

    /**
     * Parameters
     *
     * @var string[]
     */
    public $parameters;

    /**
     * content to send ("body")
     *
     * @var string|resource
     */
    public $stdin;

    /**
     * @param int                  $requestId
     * @param array|null           $params
     * @param string|resource|null $stdin string or stream resource
     */
    public function __construct ($requestId, array $params = null, $stdin = null)
    {
        $this->ID = $requestId;
        $this->parameters = $params ?: array();
        $this->stdin = $stdin ?: '';
    }
}
