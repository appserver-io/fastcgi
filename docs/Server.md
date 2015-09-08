# Server

The server is based on [`reactphp/socket`](https://github.com/reactphp/socket). This 
means you need an `EventLoop` and a `SocketServer` before you can use the server implementation.

```php
use Crunch\FastCGI\Server\Server as FastCGIServer;
use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\Server as SocketServer;

$loop = EventLoopFactory::create();
$socket = new SocketServer($loop);

$server = new FastCGIServer($socket);
```

The `EventLoopFactory` tries to find the most appropiate event loop implementation
for your local environment. See [`reactphp/event-loop`](https://github.com/reactphp/event-loop)
for further information.

After you successfully created the `FastCGIServer` instance your should register an
event listener waiting for `request` events. If you miss this, nothing will happen
and the clients will block forever.

```php
$server->on('request', function (Request $r, callable $receiver) {
    // Do something very useful

    $response = new Response($r->getRequestId(), new StringReader('foo'), new StringReader('bar'));

    $receiver($response);
});
```

As you can see the listener receives two arguments: `$request` contains the actual
request. `$receiver` is a callback, that expects the response. There are some rules to keep in mind,
especially because one can register as many listener as one like.

- At least as long as there is no error `$receiver` should receive a response, else
  depending on the configuration and the request the client may block and wait for a response
  forever.
- There must be no more than one response for a request. This usually leads to a "unexpected
  request id" within the client and in best case the unexpected response is simply
  and silently dropped.
  
| The approach of using event listeners to handle responses is still under investigation
| and may change before release of 2.0 once more.
  
As last step set the port to specify the port of the socket to listen on and start the loop.

```php
$socket->listen(9000);
$loop->run();
```

If you want to bind the server to one specific IP, you can specify it as second argument
of `listen()`

```php
$socket->listen(9000, '192.168.0.1');
```
