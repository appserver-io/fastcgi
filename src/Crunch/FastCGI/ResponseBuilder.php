<?php
namespace Crunch\FastCGI;

class ResponseBuilder {
    public $isComplete = false;
    protected $_records = array();
    public function addRecord (Record $record) {
        $this->_records[] = $record;
        if ($this->isComplete) {
            throw new \RuntimeException('Response already complete');
        }
        $this->isComplete = $record->type == Record::END_REQUEST;
    }
    public function buildResponse () {
        if (!$this->isComplete) {
            throw new \RuntimeException('Response not complete yet');
        }

        return array_reduce(
            $this->_records,
            function (Response $response, Record $record ) {
                switch ($record->type) {
                    case Record::BEGIN_REQUEST:
                    case Record::ABORT_REQUEST:
                    case Record::PARAMS:
                    case Record::STDIN:
                    case Record::DATA:
                    case Record::GET_VALUES:
                        throw new \RuntimeException('Cannot build a response from an request record');
                        break;
                    case Record::STDOUT:
                        $response->content .= $record->content;
                        break;
                    case Record::STDERR:
                        $response->error .= $record->content;
                        break;
                    case Record::END_REQUEST:
                        break;
                    case Record::GET_VALUES_RESULT:
                        break;
                    case Record::UNKNOWN_TYPE:
                        break;
                    default:
                        throw new \RuntimeException('Unknown package type received');
                        break;
                }
                return $response;
            },
            new Response
        );
    }
}
