<?php
namespace Crunch\FastCGI\Protocol;

use Traversable;

interface RequestInterface
{
    /**
     * Encodes request into an traversable of records.
     *
     * @return Traversable|Record[]
     */
    public function toRecords();
}
