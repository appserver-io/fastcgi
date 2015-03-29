<?php
namespace Crunch\FastCGI;

/**
 * ConnectionHandler interface
 *
 * Connection handlers are used as receiver in server mode. Because practically
 * every callable is suitable as connection handler this is primary a blueprint.
 * However, when you intent to implement the handler as class it is
 * recommended to implement this interface
 */
interface ConnectionHandler
{
    /**
     * Handles incoming client connections
     *
     * The handler should take care:
     * - To send an appropiate response back to the client
     * - To handle multiplexed messages. Take care of the request id
     *
     * @param Connection $connection
     * @return void
     */
    public function __invoke(Connection $connection);
}
