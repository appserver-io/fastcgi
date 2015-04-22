<?php
namespace Crunch\FastCGI;

use ArrayIterator;
use Traversable;

class Request implements RequestInterface
{
    /** @var int Request ID */
    private $ID;
    /** @var RequestParameters */
    private $parameters;
    /** @var string|resource content to send ("body") */
    private $stdin;

    /**
     * @param int $requestId
     * @param RequestParametersInterface $parameters
     * @param string|null $stdin string or stream resource
     */
    public function __construct($requestId, RequestParametersInterface $parameters = null, $stdin = null)
    {
        $this->ID = $requestId;
        $this->parameters = $parameters ?: new RequestParameters();
        $this->stdin = $stdin ?: '';
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
     * @return string
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
        $result = [new Record(new Header(Record::BEGIN_REQUEST, $this->getID(), 8), \pack('xCCxxxxx', Connection::RESPONDER, 0xFF & 1))];

        foreach ($this->getParameters()->encode($this->getID()) as $value) {
            $result[] = $value;
        }

        foreach (array_filter(str_split($this->getStdin(), 65535)) as $chunk) {
            $result[] = new Record(new Header(Record::STDIN, $this->getID(), strlen($chunk)), $chunk);
        }

        // I don't know why, but for some reason it seems, that the TCP-sockets expects
        // this to be of a certain minimum size. At least with an additional padding it works
        // with both unix- and tcp-sockets
        $result[] = new Record(new Header(Record::STDIN, $this->getID(), 0, 0), '');

        return new ArrayIterator($result);
    }
}
