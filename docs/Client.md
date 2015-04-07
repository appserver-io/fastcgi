Client
======

```php
require __DIR__ . '/../vendor/autoload.php';
use Crunch\FastCGI\Client;
use Crunch\FastCGI\ConnectionFactory;
use Socket\Raw\Factory as SocketFactory;


$socketFactory = new SocketFactory();
$connectionFactory = new ConnectionFactory($socketFactory);
$connection = $connectionFactory->connect('unix:///var/run/php5-fpm.sock');
#$connection = $connectionFactory->connect('localhost:5330');
$client = new Client($connection);
````

The `ConnectionFactory` just wraps a `\Socket\Raw\SocketFactory` instance and sets
some default values to the newly created socket.

* One `Connection` can only be used by a single `Client`
