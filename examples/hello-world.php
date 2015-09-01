<?php
require __DIR__ . '/../vendor/autoload.php';

use Crunch\FastCGI\Client\Client;

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
    $request2 = $client->newRequest(new \Crunch\FastCGI\Protocol\RequestParameters([
        'REQUEST_METHOD'  => 'POST',
        'SCRIPT_FILENAME' => __DIR__ . '/docroot/hello-world.php',
        'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
        'CONTENT_LENGTH'  => strlen($data)
    ]), new \Crunch\FastCGI\ReaderWriter\StringReader($data));
    $request3 = $client->newRequest(new \Crunch\FastCGI\Protocol\RequestParameters([
        'REQUEST_METHOD'  => 'POST',
        'SCRIPT_FILENAME' => __DIR__ . '/docroot/hello-world.php',
        'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
        'CONTENT_LENGTH'  => strlen($data)
    ]), new \Crunch\FastCGI\ReaderWriter\StringReader($data));

    $x = $client->sendRequest($request)->then(function ($response) use ($client) {
        echo "\n" . $response->getContent()->read() . \PHP_EOL;
    });
    $y = $client->sendRequest($request2)->then(function ($response) use ($client) {
        echo "\n" . $response->getContent()->read() . \PHP_EOL;
    });
    $z = $client->sendRequest($request3)->then(function ($response) use ($client) {
        echo "\n" . $response->getContent()->read() . \PHP_EOL;
    });

    $all = \React\Promise\all([$x, $y, $z]);
    $all->then(function () use ($client) {
        $client->close();
    });
});


$loop->run();
