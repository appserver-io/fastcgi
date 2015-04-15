Client
======

```php
require __DIR__ . '/../vendor/autoload.php';
use Crunch\FastCGI\Client;
use Crunch\FastCGI\ConnectionFactory;
use Socket\Raw\Factory as SocketFactory;


$socketFactory = new SocketFactory();
$clientFactory = new ClientFactory($socketFactory);
$client = $clientFactory->connect('unix:///var/run/php5-fpm.sock');
#$client = $clientFactory->connect('localhost:5330');
````

