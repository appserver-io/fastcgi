Crunch\FastCGI [![Build Status](https://secure.travis-ci.org/KingCrunch/FastCGI.png)](http://travis-ci.org/KingCrunch/FastCGI)
===
FastCGI client library

* [List of available packages at packagist.org](http://packagist.org/packages/crunch/fastcgi)

Allows to access a FastCGI-server directly from PHP.

- Subrequest
- Testing (fetch pages as seen by the webserver)
- Background tasks

Usage
=====
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
    // Much code
    $response = $connection->receiveResponse($request);

Requirements
============
* PHP => 5.3

Contributors
============
See CONTRIBUTING.md for details on how to contribute.

* Sebastian "KingCrunch" Krebs <krebs.seb@gmail.com> -- http://www.kingcrunch.de/ (german)

License
=======
This library is licensed under the MIT License. See the LICENSE file for details.
