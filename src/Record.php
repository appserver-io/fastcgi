<?php
namespace Crunch\FastCGI;

use Assert as assert;

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
    public function pack()
    {
        return $this->header->encode() . $this->getContent() . \str_repeat("\0", $this->header->getPaddingLength());
    }

    public static function unpack(Header $header, $payload)
    {
        assert\that($payload)
            ->string();

        return new Record($header, substr($payload, 0, $header->getLength()));
    }

    public static function buildFromRequest (Request $request)
    {
        $result = [new Record(new Header(1, self::BEGIN_REQUEST, $request->getID(), 8), \pack('xCCxxxxx', Connection::RESPONDER, 0xFF & 1))];

        $packet = '';
        foreach ($request->getParameters() as $name => $value) {
            $new = \pack('NN', \strlen($name) + 0x80000000, \strlen($value) + 0x80000000) . $name . $value;

            // It's possible to send up to 64kB of params in one record, but it is
            // not possible to spread a param over two records
            if (strlen($new) + strlen($packet) > 65535) {
                $result[] = new Record(new Header(1, Record::PARAMS, $request->getID(), strlen($packet)), $packet);
                $packet = '';
            }
            $packet .= $new;
        }
        $result[] = new Record(new Header(1, Record::PARAMS, $request->getID(), strlen($packet)), $packet);

        foreach (str_split($request->getStdin(), 65535) as $chunk) {
            $result[] = new Record(new Header(1, Record::STDIN, $request->getID(), strlen($chunk)), $chunk);
        }
        $result[] = new Record(new Header(1, Record::STDIN, $request->getID(), 0), '');

        return $result;
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
     * @return int
     */
    public function getType()
    {
        return $this->header->getType();
    }
}
