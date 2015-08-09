<?php
namespace Crunch\FastCGI\Connection;

/**
 * Interface ConnectionFactoryInterface
 *
 * Client and Server implementations should rely on this interface, instead
 * of the ConnectionInterface to ensure, that the connection is exclusively used
 * by this single instance.
 */
interface ConnectionFactoryInterface
{
    /**
     * Creates a new connection
     *
     * @return ConnectionInterface
     */
    public function connect();
}
