<?php
namespace Crunch\FastCGI;

use ArrayIterator;
use Traversable;

/**
 * Representing the request parameters
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
     * @return Record[]|Traversable
     */
    public function encode($requestId)
    {
        $packet = '';
        foreach ($this->parameters as $name => $value) {
            // TODO "pack(C)" when < 128
            $new = \pack('NN', \strlen($name) + 0x80000000, \strlen($value) + 0x80000000) . $name . $value;

            // Although the specs states, that it isn't important it looks like
            // at least php-fpm expects parameters not to be spread over several
            // records. That doesn't make much sense for really long values though ...
            if (strlen($new) + strlen($packet) > 65535) {
                $result[] = new Record(new Header(Record::PARAMS, $requestId, strlen($packet)), $packet);
                $packet = '';
            }
            $packet .= $new;
        }
        $result[] = new Record(new Header(Record::PARAMS, $requestId, strlen($packet)), $packet);
        // Some servers miss to drop the padding on the empty PARAMS-record
        // I look at you php-fpm ;)
        $result[] = new Record(new Header(Record::PARAMS, $requestId, 0, 0), '');

        return new ArrayIterator($result);
    }
}
