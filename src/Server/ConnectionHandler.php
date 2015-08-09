<?php
namespace Crunch\FastCGI\Server;

use React\Socket\ConnectionInterface;

class ConnectionHandler
{
    /** @var ConnectionInterface */
    private $connection;

    /**
     * ConnectionHandler constructor.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function onData($data)
    {

    }
}
