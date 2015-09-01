<?php
require __DIR__ . '/../vendor/autoload.php';

use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\Response;
use Crunch\FastCGI\ReaderWriter\StringReader;
use Crunch\FastCGI\Server\Server;

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

$server = new Server($socket);
$server->on('request', function (Request $r, callable $cb) {
    $cb(new Response(new StringReader('foo'), new StringReader('bar')));
});


$socket->listen(1337);
$loop->run();
