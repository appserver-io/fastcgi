<?php
require __DIR__ . '/../vendor/autoload.php';

use Crunch\FastCGI\Client\Client;
use Crunch\FastCGI\Client\ClientException;
use Crunch\FastCGI\Client\Factory as FastCGIClientFactory;
use Crunch\FastCGI\Protocol\RequestParameters;
use React\Dns\Resolver\Factory as DnsResolverFactory;
use React\EventLoop\Factory as EventLoopFactory;
use React\SocketClient\Connector as SocketConnector;
use React\Promise as promise;

$loop = EventLoopFactory::create();

$dnsResolverFactory = new DnsResolverFactory();
$dns = $dnsResolverFactory->createCached('0.0.0.0', $loop);

$connector = new SocketConnector($loop, $dns);

$factory = new FastCGIClientFactory($loop, $connector);

$factory->createClient('127.0.0.1', 9331)->then(function (Client $client) use ($argv) {

    $name = (@$argv[1] ?: 'World');
    $data = "name=$name";
    $request = $client->newRequest(new RequestParameters([
        'REQUEST_METHOD'  => 'POST',
        'SCRIPT_FILENAME' => __DIR__ . '/docroot/hello-world.php',
        'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
        'CONTENT_LENGTH'  => strlen($data)
    ]), new \Crunch\FastCGI\ReaderWriter\StringReader($data));
    $request2 = $client->newRequest(new RequestParameters([
        'REQUEST_METHOD'  => 'POST',
        'SCRIPT_FILENAME' => __DIR__ . '/docroot/hello-world.php',
        'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
        'CONTENT_LENGTH'  => strlen($data)
    ]), new \Crunch\FastCGI\ReaderWriter\StringReader($data));

    $responseHandler = function ($response) use ($client) {
        echo "\n" . $response->getContent()->read() . \PHP_EOL;
    };
    $failHandler = function (ClientException $fail) {
        echo "Request failed: {$fail->getMessage()}";
        return $fail;
    };

    $x = $client->sendRequest($request)->then($responseHandler, $failHandler);
    $y = $client->sendRequest($request2)->then($responseHandler, $failHandler);
    $z = $client->sendRequest($request2)->then($responseHandler, $failHandler);

    $all = promise\all([$x, $y, $z]);
    $all->then(function () use ($client) {
        $client->close();
    });
});


$loop->run();
