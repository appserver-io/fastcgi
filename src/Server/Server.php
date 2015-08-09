<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Header;
use Crunch\FastCGI\Protocol\Record;
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

    private $buffer = '';
    private $header;

    /**
     * Server constructor.
     *
     * @param ServerInterface $server
     * @param LoopInterface   $loop
     */
    public function __construct(ServerInterface $server, LoopInterface $loop)
    {
        $this->server = $server;
        $this->loop = $loop;
        $this->buffer = new RecordParser;
    }

    public function listen($address)
    {

    }

    public function run($address)
    {
        $this->server->on('connection', function (EventEmitterInterface $connection) {
            $this->handleConnection($connection);
        });

        $this->server->listen($address);

        $this->loop->run();
    }

    private function handleConnection (ConnectionInterface $connection)
    {
        $demux = new Demux();
        $connection->bufferSize = 8;
        $connection->on('data', function ($data) use ($demux) {
            if ($record = $this->buffer->pushChunk($data)) {
                $demux->pushRecord($record);
            }
        });
    }
}
