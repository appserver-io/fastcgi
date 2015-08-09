<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Header;
use Crunch\FastCGI\Protocol\Record;

class RecordParser
{
    private $buffer = '';

    /**
     * @param $chunk
     * @return Record|null
     */
    public function pushChunk ($chunk)
    {
        $this->buffer .= $chunk;

        if (strlen($this->buffer) < 8) {
            return null;
        }

        $header = Header::decode(substr($this->buffer, 0, 8));

        if (strlen($this->buffer) < $header->getPayloadLength() + 8) {
            return null;
        }

        $record = Record::decode($header, substr($this->buffer, 8, $header->getLength()) ?: '');
        $this->buffer = substr($this->buffer, 8 + $header->getPayloadLength()) ?: '';

        return $record;
    }
}
