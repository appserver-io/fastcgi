<?php
namespace Crunch\FastCGI\ReaderWriter;

use Assert as assert;

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
     *
     * @return string
     */
    public function read($max = null)
    {
        $max = $max ?: strlen($this->data);
        list($result, $this->data) = [substr($this->data, 0, $max), substr($this->data, $max)];

        return $result;
    }
}
