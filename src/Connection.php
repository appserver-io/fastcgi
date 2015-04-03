<?php
namespace Crunch\FastCGI;

use Socket\Raw\Socket;

/**
 * FastCGI Connection
 *
 * Represent to the connection to a FastCGI-server, or from a FastCGI-client.
 *
 * It uses a Socket instance, which can be blocking or non-blocking. Non-blocking
 * mode is required for asynchronous usage.
 *
 * Remember, that only one server, or client should use a Connection at one time
 * _ever_, because else you'll get issues with conflicting request ids, broken
 * multiplexing and so on.
 */
class Connection
{
    const RESPONDER = 1;
    const AUTHORIZER = 2;
    const FILTER = 3;

    /** @var Socket Stream socket to FastCGI */
    private $socket;

    /** @var int */
    private $lastRequestId = 0;

    /**
     * Creates new Connection instance
     *
     * The connection uses $socket as upstream. Every Socket should be used
     * by not more than one Connection to avoid issue when multiple connections
     * fetch records from the same stream.
     *
     * Make sure the Socket is non-blocking. You can use a blocking Socket, when
     * you don't want to use asynchronous behaviour.
     *
     * @param Socket $socket
     */
    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * Close socket
     */
    public function __destruct()
    {
        $this->socket->close();
    }

    /**
     * Send record
     *
     * Waits at most 4 seconds for the Socket to be ready for writing. This
     * is usually instant, but in some cases the send buffer may be already full
     * and therefore the request will fail with an Exception.
     *
     * If you use a blocking Socket it does not fail, but may block forever.
     *
     * @param Record $record
     * @throws \Exception
     */
    public function send(Record $record)
    {
        $this->lastRequestId = max($record->getRequestId(), $this->lastRequestId);
        if (!$this->socket->selectWrite(4)) {
            throw new \Exception('Socket not ready exception');
        }
        $this->socket->send($record->pack(), 0);
    }

    /**
     * Receive next record
     *
     * Tries to receive the next record from upstream and returns 'null', if there
     * is no complete record in the before.
     *
     * Waits at most $timeout second for the first byte to read. If you use
     * a blocking Socket timeout has no effect and it blocks until some data arrives.
     *
     * @param int $timeout
     * @return Record|null
     */
    public function receive($timeout)
    {
        if (!$this->socket->selectRead($timeout)) {
            return null;
        }

        // TODO find out, what happens, when there are _some_ bytes, but not 8 in the buffer.
        // Does it block? Probably. Better to look before.
        if (!($header = $this->socket->recv(8, \MSG_WAITALL))) {
            return null;
        }


        $header = Header::decode($header);

        $packet = $this->socket->recv($header->getLength(), \MSG_WAITALL);
        $record = Record::unpack($header, $packet);
        if ($header->getPaddingLength()) {
            $this->socket->recv($header->getPaddingLength(), \MSG_WAITALL);
        }

        $this->lastRequestId = max($record->getRequestId(), $this->lastRequestId);

        return $record;
    }
}
