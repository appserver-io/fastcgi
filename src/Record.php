<?php
namespace Crunch\FastCGI;

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

    /**
     * Record type (see constants above)
     *
     * @var int
     */
    private $type;

    /**
     * Request ID
     *
     * For received records this defines to which origin request this is
     * the answer.
     *
     * @var int
     */
    private $requestId;

    /**
     * Content received
     *
     * @var string
     */
    private $content;

    /**
     * @param int $type
     * @param int $requestId
     * @param string $content
     */
    public function __construct($type, $requestId, $content)
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
    public function pack()
    {
        $oversize = \strlen((string) $this->getContent()) % 8;
        return \pack('CCnnCx', 1, $this->getType(), $this->getRequestId(), \strlen((string) $this->getContent()), $oversize ? 8 - $oversize : 0)
            . ((string) $this->getContent()) . \str_repeat("\0", $oversize ? 8 - $oversize : 0);
    }

    public static function unpack($packet)
    {
        list ($header, $payload) = [substr($packet, 0, 8), substr($packet, 8)];
        $header = \unpack('Cversion/Ctype/nrequestId/nlength/CpaddingLength/Creserved', $header);

        return new Record($header['type'], $header['requestId'], substr($payload, 0, $header['length']));
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getRequestId()
    {
        return $this->requestId;
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
    public function getVersion()
    {
        return 1;
    }
}
