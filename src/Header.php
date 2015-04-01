<?php
namespace Crunch\FastCGI;

use Assert as assert;

class Header
{
    /** @var int */
    private $version;
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
     * @param int $version Must be 1
     * @param int $type One of the type constants
     * @param int $requestId Greater than 1
     * @param int $length Must be between 0 and 65535 (including)
     * @param int|null $paddingLength between 0 and 7
     *                                Calculated from $length when omitted, exception when invalid
     */
    public function __construct($version, $type, $requestId, $length, $paddingLength = null)
    {
        assert\that($version)
            ->integer()
            ->range(1, 1, "Only version 1 supported, $version given");
        assert\that($type)
            ->integer()
            ->range(1, 11, "Types are represented as integer between 1 and 11, $type given");
        assert\that($requestId)
            ->integer()
            ->min(1, "Request ID must be > 1, $requestId given");
        assert\that($length)
            ->integer()
            ->range(0, 65535, "Length must be between 0 and 65535, $length giben");
        assert\thatNullOr($paddingLength)
            ->integer()
            ->range(0, 7, "Padding length must be between 0 and 7");
        assert\that(is_null($paddingLength) || ($paddingLength + $length) % 8 == 0)
            ->true('Sum of Length and Padding Length must be divisible by 8');

        $this->version = $version;
        $this->type = $type;
        $this->requestId = $requestId;
        $this->length = $length;
        $this->paddingLength = $paddingLength;

        if ($length && !$paddingLength) {
            // So that "$length % $paddingLength % 8 = 0"
            $this->paddingLength = (8 - ($length % 8)) % 8;
        }
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

        return new self($header['version'], $header['type'], $header['requestId'], $header['length'], $header['paddingLength']);
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
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
        return \pack('CCnnCx', $this->getVersion(), $this->getType(), $this->getRequestId(), $this->getLength(), $this->getPaddingLength());
    }
}
