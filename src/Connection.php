<?php
namespace Crunch\FastCGI;

use Socket\Raw\Socket;

class Connection
{
    const RESPONDER = 1;
    const AUTHORIZER = 2;
    const FILTER = 3;

    /** @var Socket Stream socket to FastCGI */
    private $socket;

    /** @var RecordHandlerInterface */
    private $handler;

    /**
     * @param Socket $socket
     * @param RecordHandlerInterface $handler
     */
    public function __construct(Socket $socket, RecordHandlerInterface $handler)
    {
        $this->socket = $socket;
        $this->handler = $handler;
    }

    /**
     * Close socket
     */
    public function __destruct()
    {
        $this->socket->close();
    }

    /**
     * Send a single record
     *
     * @param Record $record
     * @throws \Exception
     */
    public function send(Record $record)
    {
        if (!$this->socket->selectWrite(4)) {
            throw new \Exception('Socket not ready exception');
        }
        $this->socket->send($record->pack(), 0);
    }

    public function receive($timeout)
    {
        while ($this->socket->selectRead($timeout) && $header = $this->socket->recv(8, \MSG_WAITALL)) {
            $header = Header::decode($header);

            $packet = $this->socket->recv($header->getPayloadLength(), \MSG_WAITALL);
            $record = Record::unpack($header, $packet);

            $this->handler->push($record);


            /*if (!isset($this->builder[$record->getRequestId()])) {
                $this->builder[$record->getRequestId()] = $this->builderFactory->create($record);
            }
            $this->builder[$record->getRequestId()]->addRecord($record);*/

            $timeout = 0; // Reset timeout to avoid stuttering on subsequent requests
        }
    }
}
