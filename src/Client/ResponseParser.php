<?php
namespace Crunch\FastCGI\Client;

use Crunch\FastCGI\Protocol\Record;
use Crunch\FastCGI\Protocol\Response;
use Crunch\FastCGI\Protocol\ResponseInterface;
use Crunch\FastCGI\ReaderWriter\StringReader;

class ResponseParser
{
    /** @var int */
    private $requestId;
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
     * @param Record $record
     *
     * @return Response|null
     * @throws \RuntimeException
     */
    public function pushRecord(Record $record)
    {
        switch (true) {
            case $record->getType()->isStdout():
                $this->stdout .= $record->getContent();
                break;
            case $record->getType()->isStderr():
                $this->stderr .= $record->getContent();
                break;
            case $record->getType()->isEndRequest():
                return new Response($this->requestId, new StringReader($this->stdout), new StringReader($this->stderr));
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown package type \'%d\'', $record->getType()));
                break;
        }
    }
}
