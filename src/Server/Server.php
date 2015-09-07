<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Header;
use Crunch\FastCGI\Protocol\Record;
use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\Response;
use Crunch\FastCGI\ReaderWriter\StringReader;
use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use React\Stream\DuplexStreamInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\Stream;

class Server implements EventEmitterInterface
{
    use EventEmitterTrait;
    /** @var ServerInterface */
    private $server;

    /**
     * Server constructor.
     *
     * @param ServerInterface $server
     * @param LoopInterface   $loop
     */
    public function __construct(ServerInterface $server)
    {
        $this->server = $server;

        $this->server->on('connection', function (ConnectionInterface $connection) {
            $this->handleConnection($connection);
        });
    }

    private function handleConnection(ConnectionInterface $connection)
    {
        $decoder = new Decoder();
        $decoder->on('request', function (Request $request) use ($connection) {
            $cb = function (Response $response) use ($connection) {
                foreach ($response->toRecords() as $r) {
                    $connection->write($r->encode());
                }
            };
            $this->emit('request', [$request, $cb]);
        });

        $connection->pipe($decoder);
    }
}
