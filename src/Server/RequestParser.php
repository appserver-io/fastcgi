<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Record;
use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\RequestInterface;
use Crunch\FastCGI\Protocol\RequestParameters;
use Crunch\FastCGI\Protocol\Role;
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
        /** @var Record $record */
        $record = array_shift($this->records);
        $role = Role::instance(ord($record->getContent()[1]));
        $keepConnection = $record->getContent()[2] !== "\x00";


        $params = '';
        while ($this->records && $this->records[0]->getType()->isParams()) {
            /** @var Record $record */
            $record = array_shift($this->records);

            $params .= $record->getContent();
        }

        $stdin = '';
        while ($this->records && $this->records[0]->getType()->isStdin()) {
            /** @var Record $record */
            $record = array_shift($this->records);

            $stdin .= $record->getContent();
        }

        if ($this->records) {
            // TODO Not empty, something went wrong
        }

        return new Request($role, $record->getRequestId(), $keepConnection, RequestParameters::decode($params), new StringReader($stdin));
    }
}
