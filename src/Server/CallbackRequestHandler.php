<?php
namespace Crunch\FastCGI\Server;

use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\RequestInterface;

class CallbackRequestHandler implements RequestHandlerInterface
{
    private $callback;

    /**
     * CallbackRequestHandler constructor.
     * @param $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }


    public function handleRequest(RequestInterface $request, Responder $responder)
    {
        $callback = $this->callback;
        $response = $callback($request);

        $responder->sendResponse($response, $request->getID());
    }
}
