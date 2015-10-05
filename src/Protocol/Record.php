<?php
namespace Crunch\FastCGI\Protocol;

use InvalidArgumentException;
use LengthException;

/**
 * Record.
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
     * @throws InvalidArgumentException Thrown when $content is not a string
     * @throws LengthException          Thrown when the length of the content does
     *                                  not match the length given by the header
     * @param Header $header
     * @param string $content
     */
    public function __construct(Header $header, $content)
    {
        if (!is_string($content)) {
            throw new InvalidArgumentException(sprintf('Content must be string, %s given', gettype($content)));
        }
        if (strlen($content) != $header->getLength()) {
            throw new LengthException('The length of the content must match the length propgated by the header');
        }

        $this->header = $header;
        $this->content = $content;
    }

    /**
     * Compiles record into struct to send.
     *
     * @return string
     */
    public function encode()
    {
        return $this->header->encode() . $this->getContent() . \str_repeat("\0", $this->header->getPaddingLength());
    }

    public static function decode(Header $header, $payload)
    {
        if (!is_string($payload)) {
            throw new InvalidArgumentException(sprintf('Payload must be a string, %s given', gettype($payload)));
        }

        return new self($header, $payload);
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
