<?php
namespace Crunch\FastCGI\Client;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\SocketClient\ConnectorInterface;
use React\Stream\DuplexStreamInterface;

class Factory
{
    /** @var LoopInterface */
    private $loop;
    /** @var ConnectorInterface */
    private $connector;

    /**
     * Factory constructor.
     *
     * @param LoopInterface      $loop
     * @param ConnectorInterface $connector
     */
    public function __construct(LoopInterface $loop, ConnectorInterface $connector)
    {
        $this->loop = $loop;
        $this->connector = $connector;
    }

    /**
     * @param string $host
     * @param int    $port
     *
     * @return PromiseInterface
     */
    public function createClient($host, $port)
    {
        return $this->connector->create($host, $port)->then(function (DuplexStreamInterface $stream) {
            return new Client($stream);
        });
    }
}
