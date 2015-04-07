<?php
namespace Crunch\FastCGI;

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
    public function pack()
    {
        return $this->header->encode() . $this->getContent() . \str_repeat("\0", $this->header->getPaddingLength());
    }

    public static function unpack(Header $header, $payload)
    {
        assert\that($payload)
            ->string();

        return new Record($header, $payload);
    }

    public static function buildFromRequest (Request $request)
    {
        $result = [new Record(new Header(1, self::BEGIN_REQUEST, $request->getID(), 8), \pack('xCCxxxxx', Connection::RESPONDER, 0xFF & 1))];

        $packet = '';
        foreach ($request->getParameters() as $name => $value) {
            // TODO "pack(C)" when < 128
            $new = \pack('NN', \strlen($name) + 0x80000000, \strlen($value) + 0x80000000) . $name . $value;

            // Although the specs states, that it isn't important it looks like
            // at least php-fpm expects parameters not to be spread over several
            // records. That doesn't make much sense for really long values though ...
            if (strlen($new) + strlen($packet) > 65535) {
                $result[] = new Record(new Header(1, Record::PARAMS, $request->getID(), strlen($packet)), $packet);
                $packet = '';
            }
            $packet .= $new;
        }
        $result[] = new Record(new Header(1, Record::PARAMS, $request->getID(), strlen($packet)), $packet);
        // Some servers miss to drop the padding on the empty PARAMS-record
        // I look at you php-fpm ;)
        $result[] = new Record(new Header(1, Record::PARAMS, $request->getID(), 0, 0), '');

        foreach (array_filter(str_split($request->getStdin(), 65535)) as $chunk) {
            $result[] = new Record(new Header(1, Record::STDIN, $request->getID(), strlen($chunk)), $chunk);
        }

        // I don't know why, but for some reason it seems, that the TCP-sockets expects
        // this to be of a certain minimum size. At least with an additional padding it works
        // with both unix- and tcp-sockets
        $result[] = new Record(new Header(1, Record::STDIN, $request->getID(), 0, 0), '');

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
