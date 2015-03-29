<?php
namespace Crunch\FastCGI;

class BuilderFactory
{
    /**
     * Creates new builder instance based on the (initial) record
     *
     * @param Record $record
     * @return Builder
     */
    public function create(Record $record)
    {
        switch ($record->getType()) {
            case Record::STDOUT:
            case Record::STDERR:
                return new ResponseBuilder();
                break;
            default:
                throw new \InvalidArgumentException("Unexpected type " . $record->getType());
        }
    }
}
