<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Record;
use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\RequestInterface;
use Crunch\FastCGI\Protocol\RequestParameters;
use Crunch\FastCGI\ReaderWriter\StringReader;

class RequestParser
{
    /** @var Record[] */
    private $records = [];

    /**
     * @param Record $record
     *
     * @return RequestInterface|null
     */
    public function pushRecord(Record $record)
    {
        $this->records[] = $record;

        if (!$record->getType()->isStdin() || $record->getContent()) {
            return;
        }

        return $this->buildRequest();
    }

    private function buildRequest()
    {
        // TODO Extend RequestInterface to handle keep-alive and other stuff as well
        $record = array_shift($this->records);
        $params = $stdin = '';

        while ($this->records && $this->records[0]->getType()->isParams()) {
            /** @var Record $record */
            $record = array_shift($this->records);

            $params .= $record->getContent();
        }
        while ($this->records && $this->records[0]->getType()->isStdin()) {
            /** @var Record $record */
            $record = array_shift($this->records);

            $stdin .= $record->getContent();
        }

        if ($this->records) {
            // TODO Not empty, something went wrong
        }

        return new Request($record->getRequestId(), RequestParameters::decode($params), new StringReader($stdin));
    }
}
