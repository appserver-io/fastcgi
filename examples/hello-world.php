<?php

use Crunch\FastCGI\Client\Client;
use Crunch\FastCGI\Connection\ConnectionFactory;
use Socket\Raw\Factory as SocketFactory;

require __DIR__ . '/../vendor/autoload.php';

$client = new Client(new ConnectionFactory('unix:///var/run/php5-fpm.sock', new SocketFactory));
$client = new Client(new ConnectionFactory('localhost:1337', new SocketFactory));


$name = (@$argv[1] ?: 'World');
$data = "name=$name";
$request = $client->newRequest(new \Crunch\FastCGI\Protocol\RequestParameters([
    'REQUEST_METHOD'  => 'POST',
    'SCRIPT_FILENAME' => __DIR__ . '/docroot/hello-world.php',
    'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
    'CONTENT_LENGTH'  => strlen($data)
]), new \Crunch\FastCGI\ReaderWriter\StringReader($data));

$client->sendRequest($request);

// Usually this ends up in an exception if there is an error, so this
// shouldn't end up in an infinite loop
while (!($response = $client->receiveResponse($request))) {
    echo '.';
}

echo "\n" . $response->getContent()->read() . \PHP_EOL;
