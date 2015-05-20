<?php
namespace Crunch\FastCGI\ReaderWriter;

class EmptyReader implements ReaderInterface
{
    public function read($max = null)
    {
        return '';
    }
}
