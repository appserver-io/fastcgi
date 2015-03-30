<?php
namespace Crunch\FastCGI;

interface RecordHandlerInterface
{
    public function push(Record $record);
}
