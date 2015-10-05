<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Header;
use Crunch\FastCGI\Protocol\Record;
use Evenement\EventEmitterTrait;
use React\Stream\WritableStreamInterface;

class Decoder implements WritableStreamInterface
{
    use EventEmitterTrait;

    private $requestReceiver;

    private $writeable = true;
    private $buffer = '';

    /** @var RequestParser[] */
    private $requestParser = [];

    /**
     * Decoder constructor.
     *
     * @param callable $requestReceiver
     */
    public function __construct(callable $requestReceiver)
    {
        $this->requestReceiver = $requestReceiver;
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
                return;
            }

            $record = Record::decode($header, substr($this->buffer, 8, $header->getLength()) ?: '');
            $this->buffer = substr($this->buffer, 8 + $header->getPayloadLength()) ?: '';

            if ($record->getType()->isBeginRequest()) {
                if (isset($this->requestParser[$record->getRequestId()])) {
                    throw new \Exception('RequestID already in use!');
                }
                $this->requestParser[$record->getRequestId()] = new RequestParser();
            }

            if ($request = $this->requestParser[$record->getRequestId()]->pushRecord($record)) {
                unset($this->requestParser[$record->getRequestId()]);

                $receiver = $this->requestReceiver;
                $receiver($request);
            }
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
