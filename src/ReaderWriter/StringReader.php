<?php
namespace Crunch\FastCGI\ReaderWriter;

class StringReader implements ReaderInterface
{
    private $data;
    private $remaining;

    /**
     * @param string $data
     */
    public function __construct($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException(sprintf('Data must be string, %s given', gettype($data)));
        }
        $this->remaining = strlen($data);

        $this->data = fopen('php://temp', 'w+');
        fwrite($this->data, $data);
        fseek($this->data, 0);
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param int|null $max
     *
     * @return string
     */
    public function read($max = null)
    {
        $max = min($max ?: PHP_INT_MAX, $this->remaining);
        if ($this->remaining <= 0 || feof($this->data)) {
            $this->close();
            return '';
        }
        $this->remaining -= $max;

        return fread($this->data, $max);
    }

    private function close()
    {
        if ($this->data) {
            fclose($this->data);
        }
    }
}
