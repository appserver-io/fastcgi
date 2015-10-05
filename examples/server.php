<?php
require __DIR__ . '/../vendor/autoload.php';

use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\RequestInterface;
use Crunch\FastCGI\Protocol\Response;
use Crunch\FastCGI\ReaderWriter\StringReader;
use Crunch\FastCGI\Server\CallableRequestHandler;
use Crunch\FastCGI\Server\Server as FastCGIServer;
use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\Server as SocketServer;

$loop = EventLoopFactory::create();
$socket = new SocketServer($loop);

$requestHandler = new CallableRequestHandler(function (RequestInterface $r, callable $cb) {
    $response = new Response($r->getRequestId(), new StringReader('foo'), new StringReader('bar'));

    $cb($response);
});

$server = new FastCGIServer($socket, $requestHandler);


$socket->listen(1337);
$loop->run();
