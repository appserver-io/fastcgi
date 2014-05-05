<?php
namespace Crunch\FastCGI;

class Client
{
    /**
     * Hostname
     *
     * May be either a host name, or a (local) path to the FCGI-socket
     *
     * - localhost
     * - unix:///var/run/php5-fpm.sock
     *
     * @var string
     */
    protected $host;

    /**
     * Port number
     *
     * Required for net-based connections, ignored for socket connections
     *
     * @var int|null
     */
    protected $port;

    /**
     * @param string   $host hostname, or path to socket
     * @param int|null $port ignored for socket connections
     */
    public function __construct($host, $port = null)
    {
        if ($host[0] == '/') {
            $host = "unix://$host";
        }
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
        if ($socket = @\fsockopen($this->host, $this->port, $errorCode, $error, 20)) {
            return new Connection($socket);
        }

        throw new ConnectionException($error, $errorCode);
    }
}
