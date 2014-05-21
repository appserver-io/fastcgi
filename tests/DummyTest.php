<?php
namespace Crunch\FastCGI;

class DummyTest extends \PHPUnit_Framework_TestCase
{
    public function testDummy ()
    {
        $client = new Client('unix:///var/run/php5-fpm.sock');
        $connection = $client->connect();
        $request = $connection->newRequest(array(
            'Foo' => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'                    => 'POST',
            'SCRIPT_FILENAME'                   => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'                      => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'                    => strlen('foo=bar')
        ), 'foo=bar');

        $connection->sendRequest($request);
        $response = $connection->receiveResponse($request);
        $response = $connection->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->content);

        list($server) = json_decode($body, true);

        $this->assertEquals(7, $server['CONTENT_LENGTH']);
    }
}
