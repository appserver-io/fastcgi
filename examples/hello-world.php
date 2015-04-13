<?php

use Crunch\FastCGI\Client;
use Crunch\FastCGI\ConnectionFactory;
use Socket\Raw\Factory as SocketFactory;

require __DIR__ . '/../vendor/autoload.php';

$socketFactory = new SocketFactory();
$connectionFactory = new ConnectionFactory($socketFactory);
$connection = $connectionFactory->connect('unix:///var/run/php5-fpm.sock');
#$connection = $connectionFactory->connect('localhost:5330');
$client = new Client($connection);


$name = (@$argv[1] ?: 'World');
$data = "name=$name";
$request = $client->newRequest(array(
    'REQUEST_METHOD'  => 'POST',
    'SCRIPT_FILENAME' => __DIR__ . '/docroot/hello-world.php',
    'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
    'CONTENT_LENGTH'  => strlen($data)
), $data);

$client->sendRequest($request);

// Usually this ends up in an exception if there is an error, so this
// shouldn't end up in an infinite loop
while (!($response = $client->receiveResponse($request))) {
    echo '.';
}

echo "\n" . $response->getContent() . \PHP_EOL;
