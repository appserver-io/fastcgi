<?php
namespace Crunch\FastCGI\Server;


use Crunch\FastCGI\Protocol\Response;
use React\Socket\Connection;
use React\Socket\ConnectionInterface;

class Responder
{
    /** @var Connection */
    private $connection;

    /**
     * Responder constructor.
     * @param Connection $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function sendResponse(Response $response, $id)
    {
        foreach ($response->toRecords($id) as $record) {
            var_dump($record->getType());
            $this->connection->write($record->encode());
        }
        $this->connection->end('');
    }
}
