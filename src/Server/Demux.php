<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Record;

class Demux
{
    /** @var RequestParser[] */
    private $requestParser = [];


    public function pushRecord (Record $record)
    {
        if ($record->getType()->isBeginRequest()) {
            if (isset($this->requestParser[$record->getRequestId()])) {
                throw new \Exception('RequestID already in use!');
            }
            $this->requestParser[$record->getRequestId()] = new RequestParser;
        }

        if ($request = $this->requestParser[$record->getRequestId()]->pushRecord($record)) {
            return $request;
        }
    }
}
