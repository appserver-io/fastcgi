<?php
namespace Crunch\FastCGI\ReaderWriter;

class VoidWriter implements WriterInterface
{
    public function write($data)
    {
        // NOOP
    }
}
