<?php
namespace Crunch\FastCGI;

use Assert as assert;

/**
 * Record header
 *
 * Speaking of the actual transmission the header is the first 8 byte sequence
 * of the byte stream. It contains the meta data consisting of the FastCGI-version
 * (constant "1"), the type of the record, the request ID, the length of the
 * content and the length of the padding sequence.
 *
 * The request ID exists to identify which response belongs to which request
 * for the server and (when multiplexing) for the client.
 *
 * The padding is zero-byte sequence. With the the actual record -- the
 * header, the content and the payload -- is always divisible by 8. However,
 * the specification allows a padding of up to 255 bytes.
 *
 * The content length is limited to 65535 bytes.
 *
 * The type defines one of the 11 record types (including "Unknown type" 11).
 */
class Header
{
    /** @var int */
    private $type;
    /** @var int */
    private $requestId;
    /** @var int */
    private $length;
    /** @var int */
    private $paddingLength;

    /**
     * Header constructor.
     *
     * If $paddingLength is omitted, it is calculated from $length. If $paddingLength
     * is set, it will be validated against $length.
     *
     * @param int $type One of the type constants
     * @param int $requestId Greater than 1
     * @param int $length Must be between 0 and 65535 (including)
     * @param int|null $paddingLength between 0 and 7
     *                                Calculated from $length when omitted, exception when invalid
     */
    public function __construct($type, $requestId, $length, $paddingLength = null)
    {
        assert\that($type)
            ->integer()
            ->range(1, 11, "Types are represented as integer between 1 and 11, $type given");
        assert\that($requestId)
            ->integer()
            ->min(1, "Request ID must be > 1, $requestId given");
        assert\that($length)
            ->integer()
            ->range(0, 65535, "Length must be between 0 and 65535, $length given");
        assert\thatNullOr($paddingLength)
            ->integer()
            ->range(0, 255, "Padding length must be between 0 and 255, $paddingLength given");
        assert\that(is_null($paddingLength) || ($paddingLength + $length) % 8 === 0)
            ->true('Sum of Length and Padding Length must be divisible by 8');

        $this->type = $type;
        $this->requestId = $requestId;
        $this->length = $length;
        $this->paddingLength = $paddingLength;

        if (!$paddingLength) {
            /* Although it is undocumented it seems, that a padding of 0 may end up
             * in a dead lock. I have tested it against php-fpm and while it worked
             * with unix sockets the server doesn't start to send any response when
             * using TCP-sockets and there are records without content and no padding.
             */
            $paddingLength = (8 - ($length % 8)) % 8;
        }
        $this->paddingLength = (int) $paddingLength;
    }

    /**
     * @param string $header
     * @return Header
     */
    public static function decode($header)
    {
        assert\that($header)
            ->string();
        assert\that(bin2hex($header))
            ->length(16);

        $header = \unpack('Cversion/Ctype/nrequestId/nlength/CpaddingLength/Creserved', $header);

        return new self($header['type'], $header['requestId'], $header['length'], $header['paddingLength']);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @return int Content length between 0 and 65535 (including)
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return int Padding length between 0 and 7 (including)
     */
    public function getPaddingLength()
    {
        return $this->paddingLength;
    }

    /**
     * @deprecated Dont know, still useful? Not used anywhere anymore
     * @return int Length of the entire payload between 0 and 65535 (including)
     */
    public function getPayloadLength()
    {
        return $this->getLength() + $this->getPaddingLength();
    }

    /**
     * Returns the encoded header as a string
     *
     * @return string
     */
    public function encode()
    {
        return \pack('CCnnCx', 1, $this->getType(), $this->getRequestId(), $this->getLength(), $this->getPaddingLength());
    }
}
