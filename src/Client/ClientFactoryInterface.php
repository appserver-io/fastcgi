<?php
namespace Crunch\FastCGI\Client;

interface ClientFactoryInterface
{
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
    public function connect($address);
}
