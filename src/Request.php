<?php
namespace Crunch\FastCGI;

class Request
{
    /**
     * Request ID
     *
     * @var int
     */
    private $ID;

    /**
     * Parameters
     *
     * @var string[]
     */
    private $parameters;

    /**
     * content to send ("body")
     *
     * @var string|resource
     */
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
