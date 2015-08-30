<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Header;
use Crunch\FastCGI\Protocol\Record;
use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\Response;
use Crunch\FastCGI\ReaderWriter\StringReader;
use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use React\Stream\DuplexStreamInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\Stream;

class Server
{
    /** @var LoopInterface */
    private $loop;
    /** @var ServerInterface */
    private $server;
    /** @var RequestHandlerInterface */
    private $handler;

    /**
     * Server constructor.
     *
     * @param ServerInterface $server
     * @param LoopInterface   $loop
     */
    public function __construct(ServerInterface $server, RequestHandlerInterface $handler, LoopInterface $loop)
    {
        $this->server = $server;
        $this->loop = $loop;
        $this->handler = $handler;
    }

    public function run($address)
    {
        $this->server->on('connection', function (EventEmitterInterface $connection) {
            $this->handleConnection($connection);
        });

        $this->server->listen($address);

        $this->loop->run();
    }

    private function handleConnection(ConnectionInterface $connection)
    {
        $connection->pipe(new Decoder(new RecordHandler($this->handler), $connection));
    }
}
