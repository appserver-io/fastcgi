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

    /** @var string */
    private $stdout = '';

    /** @var string */
    private $stderr = '';

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
    public function addRecord(Record $record)
    {
        if ($this->complete) {
            throw new \RuntimeException('Response already complete');
        }

        switch ($record->getType()) {
            case Record::STDOUT:
                $this->stdout .= $record->getContent();
                break;
            case Record::STDERR:
                $this->stderr .= $record->getContent();
                break;
            case Record::END_REQUEST:
                $this->complete = true;
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown package type \'%d\'', $record->getType()));
                break;
        }
    }

    /**
     * @return Response
     * @throws \RuntimeException
     */
    public function buildResponse()
    {
        if (!$this->complete) {
            throw new \RuntimeException('Response not complete yet');
        }

        $response = new Response($this->stdout, $this->stderr);
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
