<?php
namespace Crunch\FastCGI\Protocol;

use ArrayIterator;
use Traversable;

/**
 * Representing the request parameters.
 */
class RequestParameters implements RequestParametersInterface
{
    /** @var string[string] */
    private $parameters = [];

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @param int $requestId
     *
     * @return Record[]|Traversable
     */
    public function encode($requestId)
    {
        $packet = '';
        foreach ($this->parameters as $name => $value) {
            // TODO "pack(C)" when < 128
            // 0x80000000 => 4byte, highest bit 1, rest 0 (0x1000...0000)
            $new = \pack('NN', \strlen($name) + 0x80000000, \strlen($value) + 0x80000000) . $name . $value;

            // Although the specs states, that it isn't important it looks like
            // at least php-fpm expects parameters not to be spread over several
            // records. That doesn't make much sense for really long values though ...
            if (strlen($new) + strlen($packet) > 65535) {
                $result[] = new Record(new Header(RecordType::params(), $requestId, strlen($packet)), $packet);
                $packet = '';
            }
            $packet .= $new;
        }
        $result[] = new Record(new Header(RecordType::params(), $requestId, strlen($packet)), $packet);
        // Some servers miss to drop the padding on the empty PARAMS-record
        // I look at you php-fpm ;)
        $result[] = new Record(new Header(RecordType::params(), $requestId, 0, 0), '');

        return new ArrayIterator($result);
    }

    /**
     * @param string $data
     *
     * @return RequestParameters
     */
    public static function decode($data)
    {
        $params = [];
        while ($data) {
            // TODO take care of single byte length values
            // When the highest bit is 1 then 4 byte are used, else 1 byte
            // For both name and value
            /*if ($data[0] >> 7 === 1) {
                // 4 byte
            } else {
                // 1 byte
            }*/
            $header = \unpack('Nname/Nvalue', substr($data, 0, 8));
            $params[substr($data, 8, $header['name'] - 0x80000000)] = substr($data, 8 + $header['name'] - 0x80000000, $header['value'] - 0x80000000);
            $data = substr($data, 8 + ($header['name'] - 0x80000000) + ($header['value'] - 0x80000000));
        }

        return new self($params);
    }
}
