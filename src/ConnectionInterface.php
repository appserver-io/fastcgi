<?php
namespace Crunch\FastCGI;

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
interface ConnectionInterface
{
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
    public function send(Record $record);

    /**
     * Receive next record
     *
     * Tries to receive the next record from upstream and returns 'null', if there
     * is no complete record in the before.
     *
     * Waits at most $timeout second for the first byte to read. If you use
     * a blocking Socket timeout has no effect and it blocks until some data arrives.
     * In this case the method will always return a `Record`
     *
     * Usually you want to set $timeout to 0, so it instantly returns as long
     * as there is no data available.
     *
     * @param int $timeout
     * @return Record|null
     */
    public function receive($timeout);
}
