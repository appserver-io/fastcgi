<?php

use Crunch\FastCGI\Client\Client;
use Crunch\FastCGI\Connection\ConnectionFactory;
use Crunch\FastCGI\Protocol\Request;
use Crunch\FastCGI\Protocol\Response;
use Crunch\FastCGI\ReaderWriter\StringReader;
use Crunch\FastCGI\Server\CallbackRequestHandler;
use Crunch\FastCGI\Server\Responder;
use Socket\Raw\Factory as SocketFactory;

require __DIR__ . '/../vendor/autoload.php';



$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$connector = new React\SocketClient\Connector($loop, $dns);

$factory = new \Crunch\FastCGI\Client\Factory($loop, $connector);

$factory->createClient('127.0.0.1', 1337)->then(function (Client $client) use ($argv, $loop) {

    $name = (@$argv[1] ?: 'World');
    $data = "name=$name";
    $request = $client->newRequest(new \Crunch\FastCGI\Protocol\RequestParameters([
        'REQUEST_METHOD'  => 'POST',
        'SCRIPT_FILENAME' => __DIR__ . '/docroot/hello-world.php',
        'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
        'CONTENT_LENGTH'  => strlen($data)
    ]), new \Crunch\FastCGI\ReaderWriter\StringReader($data));

    $x = $client->sendRequest($request)->then(function ($response) use ($client) {
        var_dump($response);
        echo "\n" . $response->getContent()->read() . \PHP_EOL;
    });

    $all = \React\Promise\all([$x]);
    $all->then(function() use ($client) {
        $client->close();
    });
});


$loop->run();
