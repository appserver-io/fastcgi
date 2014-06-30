<?php
namespace Crunch\FastCGI\Mocks;

use Crunch\FastCGI\ResponseBuilder;
use Crunch\FastCGI\Response;

/**
 * Crunch\FastCGI\Mocks\MockResponseBuilder
 *
 * Mock response builder which can be illustrated to produce a certain response or time out at a certain timestamp
 */
class MockResponseBuilder extends ResponseBuilder
{
    /**
     * The injected response
     *
     * @var \Crunch\FastCGI\Response $response
     */
    protected $response = null;

    /**
     * The timeout on which the response builder sets the complete flag
     *
     * @var \Crunch\FastCGI\Response $response
     */
    protected $completeTimeout;

    /**
     * Will show if the response is complete unless is forced to time out
     *
     * @return bool
     */
    public function isComplete()
    {
        if (microtime(true) > $this->completeTimeout) {

            return true;

        } else {

            return parent::isComplete();
        }
    }

    /**
     * Allows to set a timeout on which the builder will mark a response as complete no matter what
     *
     * @param integer $timeout
     */
    public function setCompleteTimeout($timeout)
    {
        $this->completeTimeout = microtime(true) + $timeout;
    }
}
