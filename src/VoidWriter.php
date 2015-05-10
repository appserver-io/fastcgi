<?php
namespace Crunch\FastCGI;

class VoidWriter implements WriterInterface
{
    public function write($data)
    {
        // NOOP
    }
}
