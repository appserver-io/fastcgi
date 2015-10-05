<?php
namespace Crunch\FastCGI\ReaderWriter;

class StringReader implements ReaderInterface
{
    private $data;

    /**
     * @param string $data
     */
    public function __construct($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException(sprintf('Data must be string, %s given', gettype($data)));
        }

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
