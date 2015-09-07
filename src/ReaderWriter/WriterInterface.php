<?php
namespace Crunch\FastCGI\ReaderWriter;

interface WriterInterface
{
    /**
     * @param string $data
     *
     * @return void
     */
    public function write($data);
}
