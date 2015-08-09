<?php
namespace Crunch\FastCGI\Connection;

use Socket\Raw\Factory as SocketFactory;

class ConnectionFactory implements ConnectionFactoryInterface
{
    /** @var SocketFactory */
    private $socketFactory;
    private $address;

    /**
     * ConnectionFactory constructor.
     *
     * @param string $address
     * @internal param $socketFactory
     */
    public function __construct($address, SocketFactory $socketFactory)
    {
        $this->address = $address;
        $this->socketFactory = $socketFactory;
    }


    /**
     * @return ConnectionInterface
     */
    public function connect()
    {
        if (!preg_match('~^[^/]+://~', $this->address)) {
            if ($this->address[0] == '/') {
                $this->address = "unix://{$this->address}";
            }
        }

        $socket = $this->socketFactory->createClient($this->address);
        $socket->setBlocking(false);
        $socket->setOption(\SOL_SOCKET, \SO_RCVBUF, 10 * 65544);
        $socket->setOption(\SOL_SOCKET, \SO_SNDBUF, 10 * 65544);
        // Fails with 'Protocol not available (SOCKET_ENOPROTOOPT)'
        // $socket->setOption(\SOL_SOCKET, \SO_SNDLOWAT, 8);
        $socket->setOption(\SOL_SOCKET, \SO_RCVLOWAT, 8);

        return new Connection($socket);
    }
}
