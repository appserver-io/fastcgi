<?php
namespace Crunch\FastCGI;

class ResponseBuilder
{
    /** @var bool */
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

        switch (true) {
            case $record->getType()->isStdout():
                $this->stdout .= $record->getContent();
                break;
            case $record->getType()->isStderr():
                $this->stderr .= $record->getContent();
                break;
            case $record->getType()->isEndRequest():
                $this->complete = true;
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown package type \'%d\'', $record->getType()));
                break;
        }
    }

    /**
     * @return ResponseInterface
     * @throws \RuntimeException
     */
    public function build()
    {
        if (!$this->complete) {
            throw new \RuntimeException('Response not complete yet');
        }

        $response = new Response(new StringReader($this->stdout), new StringReader($this->stderr));
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
