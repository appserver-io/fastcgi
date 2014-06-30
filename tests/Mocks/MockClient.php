<?php
namespace Crunch\FastCGI\Mocks;

use Crunch\FastCGI\Client;
use Crunch\FastCGI\ConnectionException;

/**
 * Crunch\FastCGI\Mocks\MockClient
 *
 * Client mock which allows us to get a Connection mock on connection
 */
class MockClient extends Client
{
    /**
     * Connects to the server using our mock connection
     *
     * @return \Crunch\FastCGI\Mocks\MockConnection
     * @throws \Crunch\FastCGI\ConnectionException
     */
    public function connect ()
    {
        if ($socket = @\fsockopen($this->host, $this->port, $errorCode, $error, 20)) {
            return new MockConnection($socket);
        }

        throw new ConnectionException($error, $errorCode);
    }
}
