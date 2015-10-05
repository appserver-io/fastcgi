<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\RequestInterface;
use Crunch\FastCGI\Protocol\Response;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;

class Server implements EventEmitterInterface
{
    use EventEmitterTrait;
    /** @var ServerInterface */
    private $server;

    /** @var RequestHandlerInterface */
    private $requestHandler;

    /**
     * Server constructor.
     *
     * @param ServerInterface         $server
     * @param RequestHandlerInterface $requestHandler
     */
    public function __construct(ServerInterface $server, RequestHandlerInterface $requestHandler)
    {
        $this->server = $server;
        $this->requestHandler = $requestHandler;

        $this->server->on('connection', function (ConnectionInterface $connection) {
            $this->handleConnection($connection);
        });
    }

    private function handleConnection(ConnectionInterface $connection)
    {
        $decoder = new Decoder(function (RequestInterface $request) use ($connection) {
            $cb = function (Response $response) use ($connection) {
                foreach ($response->toRecords() as $r) {
                    $connection->write($r->encode());
                }
            };

            $this->requestHandler->handle($request, $cb);

        });

        $connection->pipe($decoder);
    }
}
