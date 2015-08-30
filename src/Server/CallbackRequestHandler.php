<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\RequestInterface;
use Crunch\FastCGI\Protocol\Response;
use Crunch\FastCGI\Protocol\ResponseInterface;
use React\Socket\ConnectionInterface;

class CallbackRequestHandler implements RequestHandlerInterface
{
    private $callback;

    /**
     * CallbackRequestHandler constructor.
     * @param $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }


    public function handleRequest(RequestInterface $request, ConnectionInterface $connection)
    {
        $callback = $this->callback;
        /** @var Response $response */
        $response = $callback($request);

        foreach ($response->toRecords($request->getID()) as $record) {
            var_dump($record->getType());
            $connection->write($record->encode());
        }
        $connection->end('');
    }
}
