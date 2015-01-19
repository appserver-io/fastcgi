<?php
namespace Crunch\FastCGI;

class ResponseBuilder
{
    /**
     * Whether or not the response is complete
     *
     * @var bool
     */
    private $complete = false;

    /**
     * @var Record[]
     */
    private $records = [];

    /**
     * @return boolean
     */
    public function isComplete()
    {
        return $this->complete;
    }



    /**
     * @param Record $record
     * @throws \RuntimeException
     */
    public function addRecord (Record $record)
    {
        $this->records[] = $record;
        if ($this->complete) {
            throw new \RuntimeException('Response already complete');
        }
        $this->complete = $record->getType() == Record::END_REQUEST;
    }

    /**
     * @return Response
     * @throws \RuntimeException
     */
    public function buildResponse ()
    {
        if (!$this->isComplete()) {
            throw new \RuntimeException('Response not complete yet');
        }

        list($content, $error) = array_reduce(
            $this->records,
            function (array $response, Record $record) {
                switch ($record->getType()) {
                    case Record::BEGIN_REQUEST:
                    case Record::ABORT_REQUEST:
                    case Record::PARAMS:
                    case Record::STDIN:
                    case Record::DATA:
                    case Record::GET_VALUES:
                        throw new \RuntimeException('Cannot build a response from an request record');
                        break;
                    case Record::STDOUT:
                        $response[0] .= $record->getContent();
                        break;
                    case Record::STDERR:
                        $response[1] .= $record->getContent();
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
            ['', '']
        );

        return new Response($content, $error);
    }
}
