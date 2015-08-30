<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\RequestInterface;
use React\Socket\ConnectionInterface;

interface RequestHandlerInterface
{
    public function handleRequest(RequestInterface $request, ConnectionInterface $connection);
}
