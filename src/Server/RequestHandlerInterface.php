<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\RequestInterface;

interface RequestHandlerInterface
{
    public function handleRequest(RequestInterface $request, Responder $responder);
}
