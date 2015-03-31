<?php
namespace Crunch\FastCGI;

class Header
{
    private $version;
    private $type;
    private $requestId;
    private $length;
    private $paddingLength;

    /**
     * Header constructor.
     *
     * If $paddingLength is omitted, it is calculated from $length. If $paddingLength
     * is set, it will be validated against $length.
     *
     * @param int $version
     * @param int $type
     * @param int $requestId
     * @param int $length
     * @param int|null $paddingLength Calculated from $length when omitted, exception when invalid
     */
    public function __construct($version, $type, $requestId, $length, $paddingLength = null)
    {
        $this->version = $version;
        $this->type = $type;
        $this->requestId = $requestId;
        $this->length = $length;
        $this->paddingLength = $paddingLength;

        if ($length && !$paddingLength) {
            $this->paddingLength = (8 - ($length % 8)) % 8;
        }
    }

    /**
     * @param string $header
     * @return Header
     */
    public static function decode($header)
    {
        $header = \unpack('Cversion/Ctype/nrequestId/nlength/CpaddingLength/Creserved', $header);

        return new self($header['version'], $header['type'], $header['requestId'], $header['length'], $header['paddingLength']);
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return mixed
     */
    public function getPaddingLength()
    {
        return $this->paddingLength;
    }

    public function getPayloadLength()
    {
        return $this->getLength() + $this->getPaddingLength();
    }

    public function encode()
    {
        return \pack('CCnnCx', $this->getVersion(), $this->getType(), $this->getRequestId(), $this->getLength(), $this->getPaddingLength());
    }
}
