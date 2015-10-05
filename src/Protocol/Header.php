<?php
namespace Crunch\FastCGI\Protocol;
use DomainException;
use InvalidArgumentException;
use LengthException;

/**
 * Record header.
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
    /** @var RecordType */
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
     * @throws InvalidArgumentException thrown when one argument is of an invalid type
     * @throws DomainException thrown when the value of an argument is invalid
     * @param RecordType $type
     * @param int        $requestId     Greater than 1
     * @param int        $length        Must be between 0 and 65535 (including)
     * @param int|null   $paddingLength between 0 and 7
     *                                  Calculated from $length when omitted, exception when invalid
     */
    public function __construct(RecordType $type, $requestId, $length, $paddingLength = null)
    {
        if (!is_int($requestId)) {
            throw new InvalidArgumentException(sprintf('Request ID must be an integer, %s given', gettype($requestId)));
        }
        if ($requestId <= 0) {
            throw new DomainException("Request ID must be a positive integer, $requestId given");
        }

        if (!is_int($length)) {
            throw new InvalidArgumentException(sprintf('Length must be an integer, %s given', gettype($length)));
        }
        if (0 > $length || $length > 65535) {
            throw new DomainException("Length must be between 0 and 65535, $length given");
        }

        if (!is_null($paddingLength) && !is_int($paddingLength)) {
            throw new InvalidArgumentException(sprintf('Padding length must be an integer, or null, %s given', gettype($paddingLength)));
        }
        if (!is_null($paddingLength) && (0 > $paddingLength || $paddingLength > 255)) {
            throw new DomainException("Padding Lenght must be null or between 0 and 255, $length given");
        }
        if (!is_null($paddingLength) && ($paddingLength + $length) % 8 !== 0) {
            throw new DomainException(sprintf('Padding Lenght must be null or Padding Length + Length must be divisable by 8, %d given', $paddingLength + $length));
        }

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
     *
     * @throws InvalidArgumentException thrown when $header is not a string
     * @throws LengthException thrown when $header is not exactly 8 bytes
     * @return Header
     */
    public static function decode($header)
    {
        if (!is_string($header)) {
            throw new InvalidArgumentException(sprintf('Header must be a (binary) string, %s given', gettype($header)));
        }
        if (strlen($header) != 8) {
            throw new LengthException(sprintf('Header must be exactly 8 bytes, %d bytes given', strlen($header)));
        }

        $header = \unpack('Cversion/Ctype/nrequestId/nlength/CpaddingLength/Creserved', $header);

        return new self(RecordType::instance($header['type']), $header['requestId'], $header['length'], $header['paddingLength']);
    }

    /**
     * @return RecordType
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
     *
     * @return int Length of the entire payload between 0 and 65535 (including)
     */
    public function getPayloadLength()
    {
        return $this->getLength() + $this->getPaddingLength();
    }

    /**
     * Returns the encoded header as a string.
     *
     * @return string
     */
    public function encode()
    {
        return \pack('CCnnCx', 1, $this->getType()->value(), $this->getRequestId(), $this->getLength(), $this->getPaddingLength());
    }
}
