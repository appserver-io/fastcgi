<?php
namespace Crunch\FastCGI;

class Request
{
    /** @var int Request ID */
    private $ID;
    /** @var string[] */
    private $parameters;
    /** @var string|resource content to send ("body") */
    private $stdin;

    /**
     * @param int           $requestId
     * @param string[]|null $params
     * @param string|null   $stdin string or stream resource
     */
    public function __construct($requestId, array $params = null, $stdin = null)
    {
        $this->ID = $requestId;
        $this->parameters = $params ?: [];
        $this->stdin = $stdin ?: '';
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * @return \string[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getStdin()
    {
        return $this->stdin;
    }
}
