<?php
namespace Crunch\FastCGI\Client;

use Crunch\FastCGI\Connection\Connection;
use Socket\Raw\Factory as SocketFactory;

class ClientFactory implements ClientFactoryInterface
{
    /** @var SocketFactory */
    private $socketFactory;

    /**
     * @param SocketFactory $socketFactory
     */
    public function __construct(SocketFactory $socketFactory)
    {
        $this->socketFactory = $socketFactory;
    }

    /**
     * Connects to a server
     *
     * Subsequent calls will always open a new connection.
     *
     * It tries to find out itself, whether or not $address is a unix-, or a tcp-socket. If you want to get sure,
     * you should always prepend "unix://", or "tcp://"
     *
     * @param string $address <tcp://>hostname[:port] or UNIX-path <unix://>/path/to/socket
     * @return ClientInterface
     * @throws \RuntimeException
     */
    public function connect($address)
    {
        if (!preg_match('~^[^/]+://~', $address)) {
            if ($address[0] == '/') {
                $address = "unix://$address";
            }
        }

        $socket = $this->socketFactory->createClient($address);
        $socket->setBlocking(false);
        $socket->setOption(\SOL_SOCKET, \SO_RCVBUF, 10 * 65544);
        $socket->setOption(\SOL_SOCKET, \SO_SNDBUF, 10 * 65544);
        // Fails with 'Protocol not available (SOCKET_ENOPROTOOPT)'
        // $socket->setOption(\SOL_SOCKET, \SO_SNDLOWAT, 8);
        $socket->setOption(\SOL_SOCKET, \SO_RCVLOWAT, 8);

        return new Client(new Connection($socket));
    }
}
