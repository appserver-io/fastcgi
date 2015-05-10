<?php
namespace Crunch\FastCGI;

use Assert as assert;
use Crunch\FastCGI\Strings as str;

class StringReader implements ReaderInterface
{
    private $data;

    /**
     * @param string $data
     */
    public function __construct($data)
    {
        assert\that($data)->string();

        $this->data = $data;
    }

    /**
     * @param int|null $max
     * @return string
     */
    public function read($max = null)
    {
        $max = $max ?: strlen($this->data);
        list($result, $this->data) = str\cut($this->data, $max);

        return $result;
    }
}
