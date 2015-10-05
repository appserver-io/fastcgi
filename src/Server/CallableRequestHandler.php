<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\RequestInterface;

class CallableRequestHandler implements RequestHandlerInterface
{
    private $callable;

    /**
     * CallableRequestHandler constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }


    public function handle(RequestInterface $request, callable $receiver)
    {
        $callable = $this->callable;
        $callable($request, $receiver);
    }
}
