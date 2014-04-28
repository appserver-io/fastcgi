<?php
namespace Crunch\FastCGI;

/**
 * Client
 *
 * @package Crunch\FastCGI
 */
class Client
{
    /**
     * Hostname
     *
     * May be either a host name, or a (local) path to the FCGI-socket
     *
     * - localhost
     * - /var/run/php5-fpm.sock
     *
     * @var string
     */
    protected $host;

    /**
     * Port number
     *
     * Required for net-based connections, ignored for socket connectios
     *
     * @var int|null
     */
    protected $port;

    /**
     * @param string $host hostname, or path to socket
     * @param int|null $port ignored for socket connections
     */
    public function __construct($host, $port = null)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Connects to the server
     *
     * Subsequent calls will always open a new connection
     *
     * @return Connection
     * @throws \RuntimeException
     */
    public function connect ()
    {
        if ($socket = fsockopen($this->host, $this->port, $errorCode, $error, 20)) {
            return new Connection($socket);
        }

        throw new \RuntimeException("Could not establish connection: $error", $errorCode);
    }
}
