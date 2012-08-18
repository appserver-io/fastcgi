Crunch\FastCGI
===
FastCGI client written in PHP

Installation
===
Standalone
---
    git clone git://github.com/KingCrunch/FastCGI.git
    php composer.phar install

Within your application

    require '/path/to/crunch/fastcgi/vendor/autoload.php';
    // use it (see below)

Embedded
---
Add it to your `composer.json`

    "require": {
        "crunch/fastcgi": "0.1.*-dev"
    }

Example
---

    require __DIR__ . '/../vendor/autoload.php';
    use Crunch\FastCGI\Client as FastCGI;
    use Crunch\FastCGI\Request as FCGIRequest;


    $fastCgi = new FastCGI('unix:///var/run/php5-fpm.socket', null);
    // $fastCgi = new FastCGI('localhost', 9999);
    $connection = $fastCgi->connect();

    $request = new FCGIRequest;
    $request->parameters = array(
        'GATEWAY_INTERFACE' => 'FastCGI/1.0',
        'REQUEST_METHOD' => 'POST',
        'SCRIPT_FILENAME' => '/var/www/example.php',
        'SERVER_SOFTWARE' => 'php/cruch-fastcgi',
        'REMOTE_ADDR' => '127.0.0.1',
        'REMOTE_PORT' => '9985',
        'SERVER_ADDR' => '127.0.0.1',
        'SERVER_PORT' => '80',
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        'CONTENT_LENGTH' => strlen('foo=bar')
    );
    $request->stdin = 'foo=bar';
    $response = $connection->sendRequest($request);
    echo $response->content;
