<?php
namespace Crunch\FastCGI;

class Record implements \Countable
{
    const BEGIN_REQUEST = 1;
    const ABORT_REQUEST = 2;
    const END_REQUEST = 3;
    const PARAMS = 4;
    const STDIN = 5;
    const STDOUT = 6;
    const STDERR = 7;
    const DATA = 8;
    const GET_VALUES = 9;
    const GET_VALUES_RESULT = 10;
    const UNKNOWN_TYPE = 11;
    const MAXTYPE = self::UNKNOWN_TYPE;

    /**
     * FastCGI version
     *
     * @var int
     */
    public $version = 1;

    /**
     * Record type (see constants above)
     *
     * @var int
     */
    public $type;

    /**
     * Request ID
     *
     * For received records this defines to which origin request this is
     * the answer.
     *
     * @var int
     */
    public $requestId;

    /**
     * Content received
     *
     * @var string
     */
    public $content;

    /**
     * @param int $type
     * @param int $requestId
     * @param string $content
     */
    public function __construct ($type, $requestId, $content)
    {
        $this->type = $type;
        $this->requestId = $requestId;
        $this->content = $content;
    }

    /**
     * Compiles record into struct to send
     *
     * @return string
     */
    public function pack ()
    {
        $oversize = \strlen((string) $this->content) % 8;
        return \pack('CCnnCx', 1, $this->type, $this->requestId, \strlen((string) $this->content), $oversize ? 8 - $oversize : 0)
            . ((string) $this->content) . \str_repeat("\0", $oversize ? 8 - $oversize : 0);
    }

    /**
     * To string
     *
     * Proxy to "pack()"
     *
     * @return string
     */
    public function __toString ()
    {
        return $this->pack();
    }

    /**
     * Length of the body
     *
     * @return int
     */
    public function count ()
    {
        return \strlen($this->pack());
    }

    /**
     * Whether, or not this record is sendable
     *
     * "false" means, that it is a receive-only record.
     *
     * @return bool
     */
    public function isSendable ()
    {
        return \in_array(
            $this->type,
            array(self::BEGIN_REQUEST, self::ABORT_REQUEST, self::PARAMS, self::STDIN, self::DATA, self::GET_VALUES)
        );
    }
}
