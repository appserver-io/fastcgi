<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Header;
use Crunch\FastCGI\Protocol\Record;
use Evenement\EventEmitterTrait;
use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;

class Decoder implements WritableStreamInterface
{
    use EventEmitterTrait;

    private $writeable = true;
    private $buffer = '';

    private $recordHandler;
    private $connection;

    /**
     * Decoder constructor.
     * @param $recordHandler
     */
    public function __construct(RecordHandler $recordHandler, ConnectionInterface $connection)
    {
        $this->recordHandler = $recordHandler;
        $this->connection = $connection;
    }


    public function isWritable()
    {
        return $this->writeable;
    }

    public function write($data)
    {
        if (!$this->writeable) {
            return;
        }

        $this->buffer .= $data;

        while (strlen($this->buffer) >= 8) {
            $header = Header::decode(substr($this->buffer, 0, 8));


            if (strlen($this->buffer) < $header->getPayloadLength() + 8) {
                return null;
            }

            $record = Record::decode($header, substr($this->buffer, 8, $header->getLength()) ?: '');
            $this->buffer = substr($this->buffer, 8 + $header->getPayloadLength()) ?: '';

            $this->recordHandler->pushRecord($record, $this->connection);
        }
    }

    public function end($data = null)
    {
        if ($data) {
            $this->write($data);
        }
        $this->close();
    }

    public function close()
    {
        $this->writeable = false;
    }
}
