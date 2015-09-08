# CLient


The client is based on [`reactphp/socket-client`](https://github.com/reactphp/socket-client). This 
means you need an `EventLoop` , a `DnsResolverFactory` and a `SocketConnector` before you can 
use the client implementation.

```php
use Crunch\FastCGI\Client\Factory as FastCGIClientFactory;
use React\Dns\Resolver\Factory as DnsResolverFactory;
use React\EventLoop\Factory as EventLoopFactory;
use React\SocketClient\Connector as SocketConnector;

$loop = EventLoopFactory::create();

$dnsResolverFactory = new DnsResolverFactory();
$dns = $dnsResolverFactory->createCached('8.8.4.4', $loop);

$connector = new SocketConnector($loop, $dns);

$factory = new FastCGIClientFactory($loop, $connector);
````

If you already know, that you'll never connect to a FastCGI-server via hostname, for example
when you always connect to `127.0.0.1`, you can set up any IP address, for example 
`0.0.0.0`. However, to avoid confusion later I'd recommend to point the resolver
to a real DNS-server and in best case use one under your own control.

This implementation uses [`reactphp/promise`](https://github.com/reactphp/promise). Because
of this you'll usually not retrieve the actual client directly, but instead interact with
it right after the connection is established.

```php
use Crunch\FastCGI\Protocol\RequestParameters;
use Crunch\FastCGI\Client\ClientException;
use React\Promise as promise;

$factory->createClient('127.0.0.1', 9331)->then(function (Client $client) use ($argv) {
    $name = (@$argv[1] ?: 'World');
    $data = "name=$name";
    $request = $client->newRequest(new RequestParameters([
        'REQUEST_METHOD'  => 'POST',
        'SCRIPT_FILENAME' => __DIR__ . '/docroot/hello-world.php',
        'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
        'CONTENT_LENGTH'  => strlen($data)
    ]), new \Crunch\FastCGI\ReaderWriter\StringReader($data));

    $responseHandler = function ($response) {
        echo "\n" . $response->getContent()->read() . \PHP_EOL;
    };
    $failHandler = function (ClientException $fail) {
        echo "Request failed: {$fail->getMessage()}";
        return $fail;
    };

    $x = $client->sendRequest($request)->then($responseHandler, $failHandler);

    promise\all([$x])->then(function () use ($client) {
        $client->close();
    });
});
```

The call of `promise/all()` takes care, that all sent request are handled, before
the connection is closed. If you only have a single request, you can also close
the connection directly within the response handler instead.

```php
$responseHandler = function ($response) use ($client) {
    echo "\n" . $response->getContent()->read() . \PHP_EOL;
    
    $client->close();
};
```

| `keepAlive=false` would do the same, but is not yet implemented. This option will
| tell the server to close the connection right after the response was sent.

The last step, of course: Run the loop

```php
$loop->run();
```
