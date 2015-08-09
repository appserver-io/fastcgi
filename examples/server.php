<?php


use Crunch\FastCGI\Server\Server;

require __DIR__ . '/../vendor/autoload.php';


$loop = React\EventLoop\Factory::create();

$socket = new React\Socket\Server($loop);

$server = new Server($socket, $loop);


$server->run(1337);
