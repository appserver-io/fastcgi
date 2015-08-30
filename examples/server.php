<?php


use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\Response;
use Crunch\FastCGI\ReaderWriter\StringReader;
use Crunch\FastCGI\Server\CallbackRequestHandler;
use Crunch\FastCGI\Server\Responder;
use Crunch\FastCGI\Server\Server;

require __DIR__ . '/../vendor/autoload.php';


$handler = new CallbackRequestHandler(function(Request $request) {
    return new Response(new StringReader('foo'), new StringReader('bar'));
});


$loop = React\EventLoop\Factory::create();

$socket = new React\Socket\Server($loop);

$server = new Server($socket, $handler, $loop);


$server->run(1337);
