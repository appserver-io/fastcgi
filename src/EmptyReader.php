<?php
namespace Crunch\FastCGI;

class EmptyReader implements ReaderInterface
{
    public function read($max = null)
    {
        return '';
    }
}
