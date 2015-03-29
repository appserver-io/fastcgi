<?php
namespace Crunch\FastCGI;

interface Builder
{
    public function addRecord(Record $record);
    public function build();
}
