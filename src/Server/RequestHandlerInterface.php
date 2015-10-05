<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\RequestInterface;

interface RequestHandlerInterface
{
    public function handle(RequestInterface $request, callable $receiver);
}
