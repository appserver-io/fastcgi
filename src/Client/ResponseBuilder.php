<?php
namespace Crunch\FastCGI\Client;

use Crunch\FastCGI\Protocol\Record;
use Crunch\FastCGI\Protocol\Response;
use Crunch\FastCGI\Protocol\ResponseInterface;
use Crunch\FastCGI\ReaderWriter\StringReader;

class ResponseBuilder
{
    /** @var int */
    private $requestId;
    /** @var bool */
    private $complete = false;
    /** @var string */
    private $stdout = '';
    /** @var string */
    private $stderr = '';

    /**
     * @param int $requestId
     */
    public function __construct($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return $this->complete;
    }

    /**
     * @param Record $record
     *
     * @throws \RuntimeException
     */
    public function addRecord(Record $record)
    {
        if ($this->complete) {
            throw new \RuntimeException('Response already complete');
        }

        switch (true) {
            case $record->getType()->isStdout():
                $this->stdout .= $record->getContent();
                break;
            case $record->getType()->isStderr():
                $this->stderr .= $record->getContent();
                break;
            case $record->getType()->isEndRequest():
                $this->complete = true;
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown package type \'%d\'', $record->getType()));
                break;
        }
    }

    /**
     * @throws \RuntimeException
     *
     * @return ResponseInterface
     */
    public function build()
    {
        if (!$this->complete) {
            throw new \RuntimeException('Response not complete yet');
        }

        $response = new Response($this->requestId, new StringReader($this->stdout), new StringReader($this->stderr));
        $this->reset();

        return $response;
    }

    public function reset()
    {
        $this->stdout = '';
        $this->stderr = '';
        $this->complete = false;
    }
}
