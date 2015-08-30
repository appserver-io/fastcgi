<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Record;
use React\Socket\ConnectionInterface;

class RecordHandler
{
    /** @var RequestParser[] */
    private $requestParser = [];
    /** @var RequestHandlerInterface */
    private $requestHandler;

    /**
     * RecordHandler constructor.
     * @param RequestHandlerInterface $requestHandler
     */
    public function __construct(RequestHandlerInterface $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    public function pushRecord(Record $record, ConnectionInterface $connection)
    {
        if ($record->getType()->isBeginRequest()) {
            if (isset($this->requestParser[$record->getRequestId()])) {
                throw new \Exception('RequestID already in use!');
            }
            $this->requestParser[$record->getRequestId()] = new RequestParser;
        }

        if ($request = $this->requestParser[$record->getRequestId()]->pushRecord($record)) {
            unset($this->requestParser[$record->getRequestId()]);
            $this->requestHandler->handleRequest($request, $connection);
        }
    }
}
