<?php
namespace Crunch\FastCGI;

use Socket\Raw\Factory as SocketFactory;

class ConnectionFactory
{
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
     * @param string $address hostname[:port] or UNIX-path
     * @return Connection
     * @throws \RuntimeException
     */
    public function connect($address)
    {
        if (!preg_match('~^[^/]+://~', $address) && strpos($address, '/')) {
            $address = "unix://$address";
        }
        return new Connection($this->socketFactory->createClient($address));
    }
}
