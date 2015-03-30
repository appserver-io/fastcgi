<?php
namespace Crunch\FastCGI;

class ClientRecordHandler implements RecordHandlerInterface
{
    /** @var ResponseBuilder[] */
    private $expectedResponses = [];
    public function expectResponse($recordId)
    {
        $this->expectedResponses[$recordId] = new ResponseBuilder;
    }
    public function push(Record $record)
    {
        if (!array_key_exists($record->getRequestId(), $this->expectedResponses)) {
            throw new \Exception('Unexpected response id ' . $record->getRequestId());
        }

        $this->expectedResponses[$record->getRequestId()]->addRecord($record);
    }

    public function isComplete($recordId)
    {
        return $this->expectedResponses[$recordId]->isComplete();
    }

    public function createResponse($recordId)
    {
        return $this->expectedResponses[$recordId]->build();
    }
}
