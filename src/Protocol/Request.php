<?php
namespace Crunch\FastCGI\Protocol;

use ArrayIterator;
use Crunch\FastCGI\Connection\Connection;
use Crunch\FastCGI\ReaderWriter\EmptyReader;
use Crunch\FastCGI\ReaderWriter\ReaderInterface;
use Traversable;

class Request implements RequestInterface
{
    /** @var int Request ID */
    private $ID;
    /** @var RequestParameters */
    private $parameters;
    /** @var \Crunch\FastCGI\ReaderWriter\ReaderInterface content to send ("body") */
    private $stdin;

    /**
     * @param int $requestId
     * @param RequestParametersInterface|null $parameters
     * @param \Crunch\FastCGI\ReaderWriter\ReaderInterface|null $stdin string or stream resource
     */
    public function __construct($requestId, RequestParametersInterface $parameters = null, ReaderInterface $stdin = null)
    {
        $this->ID = $requestId;
        $this->parameters = $parameters ?: new RequestParameters;
        $this->stdin = $stdin ?: new EmptyReader;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * @return RequestParameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return ReaderInterface
     */
    public function getStdin()
    {
        return $this->stdin;
    }

    /**
     * Encodes request into an traversable of records
     *
     * @return Traversable|Record[]
     */
    public function toRecords()
    {
        $result = [new Record(new Header(RecordType::beginRequest(), $this->getID(), 8), \pack('xCCxxxxx', Role::RESPONDER, 0xFF & 1))];

        foreach ($this->getParameters()->encode($this->getID()) as $value) {
            $result[] = $value;
        }

        while ($chunk = $this->stdin->read(65535)) {
            $result[] = new Record(new Header(RecordType::stdin(), $this->getID(), strlen($chunk)), $chunk);
        }

        // I don't know why, but for some reason it seems, that the TCP-sockets expects
        // this to be of a certain minimum size. At least with an additional padding it works
        // with both unix- and tcp-sockets
        $result[] = new Record(new Header(RecordType::stdin(), $this->getID(), 0, 0), '');

        return new ArrayIterator($result);
    }
}
