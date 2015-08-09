<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Record;

class Demux
{
    /** @var RequestParser[] */
    private $recordHandler = [];


    public function pushRecord (Record $record)
    {
        if ($record->getType()->isBeginRequest()) {
            if (isset($this->recordHandler[$record->getRequestId()])) {
                throw new \Exception("RequestID already in use!");
            }
            $this->recordHandler[$record->getRequestId()] = new RequestParser;
        }

        if ($request = $this->recordHandler[$record->getRequestId()]->pushRecord($record)) {
            var_dump($request);
        }
    }
}
