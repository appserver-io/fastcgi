<?php
namespace Crunch\FastCGI;

/**
 * Request
 *
 * @package Crunch\FastCGI
 */
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
     * @var string
     */
    public $stdin;

    /**
     * @param int $requestId
     * @param array|null $params
     * @param string|null  $stdin
     */
    public function __construct ($requestId, array $params = null, $stdin = null)
    {
        $this->ID = $requestId;
        $this->parameters = $params ?: array();
        $this->stdin = $stdin ?: '';
    }
}
