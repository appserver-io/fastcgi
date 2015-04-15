<?php
namespace Crunch\FastCGI;

use ArrayIterator;
use Traversable;

class Request
{
    /** @var int Request ID */
    private $ID;
    /** @var string[] */
    private $parameters;
    /** @var string|resource content to send ("body") */
    private $stdin;

    /**
     * @param int $requestId
     * @param string[]|null $params
     * @param string|null $stdin string or stream resource
     */
    public function __construct($requestId, array $params = null, $stdin = null)
    {
        $this->ID = $requestId;
        $this->parameters = $params ?: [];
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
     * @return \string[]
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
        $result = [new Record(new Header(1, Record::BEGIN_REQUEST, $this->getID(), 8), \pack('xCCxxxxx', Connection::RESPONDER, 0xFF & 1))];

        $packet = '';
        foreach ($this->getParameters() as $name => $value) {
            // TODO "pack(C)" when < 128
            $new = \pack('NN', \strlen($name) + 0x80000000, \strlen($value) + 0x80000000) . $name . $value;

            // Although the specs states, that it isn't important it looks like
            // at least php-fpm expects parameters not to be spread over several
            // records. That doesn't make much sense for really long values though ...
            if (strlen($new) + strlen($packet) > 65535) {
                $result[] = new Record(new Header(1, Record::PARAMS, $this->getID(), strlen($packet)), $packet);
                $packet = '';
            }
            $packet .= $new;
        }
        $result[] = new Record(new Header(1, Record::PARAMS, $this->getID(), strlen($packet)), $packet);
        // Some servers miss to drop the padding on the empty PARAMS-record
        // I look at you php-fpm ;)
        $result[] = new Record(new Header(1, Record::PARAMS, $this->getID(), 0, 0), '');

        foreach (array_filter(str_split($this->getStdin(), 65535)) as $chunk) {
            $result[] = new Record(new Header(1, Record::STDIN, $this->getID(), strlen($chunk)), $chunk);
        }

        // I don't know why, but for some reason it seems, that the TCP-sockets expects
        // this to be of a certain minimum size. At least with an additional padding it works
        // with both unix- and tcp-sockets
        $result[] = new Record(new Header(1, Record::STDIN, $this->getID(), 0, 0), '');

        return new ArrayIterator($result);
    }
}
