<?php
namespace Crunch\FastCGI\Protocol;

use Assert as assert;

/**
 * Record
 *
 * The record is the smallest unit in the communication between the
 * server and the client. It consists of the Header and the payload, which
 * itself consists of the actual data and some padding zero-bytes.
 */
class Record
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

    /** @var Header */
    private $header;
    /** @var string Content received */
    private $content;

    /**
     * @param Header $header
     * @param string $content
     */
    public function __construct(Header $header, $content)
    {
        assert\that($content)
            ->string();
        assert\that($content)
            ->length($header->getLength());

        $this->header = $header;
        $this->content = $content;
    }

    /**
     * Compiles record into struct to send
     *
     * @return string
     */
    public function encode()
    {
        return $this->header->encode() . $this->getContent() . \str_repeat("\0", $this->header->getPaddingLength());
    }

    public static function decode(Header $header, $payload)
    {
        assert\that($payload)
            ->string();

        return new Record($header, $payload);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return (string) $this->content;
    }

    /**
     * @return int
     */
    public function getRequestId()
    {
        return $this->header->getRequestId();
    }

    /**
     * @return RecordType
     */
    public function getType()
    {
        return $this->header->getType();
    }
}
