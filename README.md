Crunch\FastCGI
===
FastCGI client written in PHP


Allows to access a FastCGI-server directly from PHP.

- Subrequest
- Testing (fetch pages as seen by the webserver)
- Background tasks


Installation
===
Embedded using composer (recommended)
---
Add it to your `composer.json`

    "require": {
        "crunch/fastcgi": "0.1.*-dev"
    }

Standalone
---

    git clone git://github.com/KingCrunch/FastCGI.git
    php composer.phar install

Within your application

    require '/path/to/crunch/fastcgi/vendor/autoload.php';

Standalone using composer
---

    composer.phar create-project crunch/fastcgi

This will create a new folder `fastcgi`. Again within your application

    require '/path/to/crunch/fastcgi/vendor/autoload.php';


Example
---

Preparation

    require __DIR__ . '/../vendor/autoload.php';
    use Crunch\FastCGI\Client as FastCGI;


    $fastCgi = new FastCGI('unix:///var/run/php5-fpm.socket', null);
    // $fastCgi = new FastCGI('localhost', 9999);
    $connection = $fastCgi->connect();

The request

    $request = $connection->newRequest();
    $request->parameters = array(
        'GATEWAY_INTERFACE' => 'FastCGI/1.0',
        'REQUEST_METHOD' => 'POST',
        'SCRIPT_FILENAME' => '/var/www/example.php',
        'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        'CONTENT_LENGTH' => strlen('foo=bar')
    );
    $request->stdin = 'foo=bar';

Single request and response

    $response = $connection->request($request);
    echo $response->content;

Background (includes multiplexing)

    $connection->sendRequest($request);
    // Many code
    $response = $connection->receiveResponse($request);
