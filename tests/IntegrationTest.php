<?php
namespace Crunch\FastCGI;

use Phake;
use Phake_IMock as Mock;
use Socket\Raw\Factory;

/**
 * @coversNothing
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /** @var Mock|Factory */
    private $socketFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->socketFactory = Phake::partialMock('\Socket\Raw\Factory');


        $conf = __DIR__ . '/Resources/php-fpm.conf';
        // start fpm daemon
        exec("`which php5-fpm` -n -y $conf -p " . __DIR__ . '/tmp 2>&1 1>/dev/null');

        // wait until pid file is generate
        while (!is_file(__DIR__ . '/tmp/php5-fpm.pid')) {
            usleep(100000);
        }
    }

    public function tearDown()
    {
        parent::tearDownAfterClass();

        if (file_exists(__DIR__ . '/tmp/php5-fpm.pid')) {
            exec(sprintf('kill %d', file_get_contents(__DIR__ . '/tmp/php5-fpm.pid')));

            while (file_exists(__DIR__ . '/tmp/php5-fpm.pid')) {
                usleep(100000);
            }
        }

        if (file_exists(__DIR__ . '/tmp/php5-fpm.log')) {
            unlink(__DIR__ . '/tmp/php5-fpm.log');
        }
    }


    public function testSendSimpleRequest()
    {
        $client = new ConnectionFactory($this->socketFactory);
        $connection = $client->connect('localhost:9331');
        $request = $connection->newRequest(array(
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ), 'foo=bar');

        $connection->sendRequest($request);
        $response = $connection->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->getContent());

        list($server) = unserialize($body);

        $this->assertEquals(7, $server['CONTENT_LENGTH']);
    }


    public function testSendSimpleRequestWithOversizedPayload()
    {
        $client = new ConnectionFactory($this->socketFactory);
        $connection = $client->connect('localhost:9331');

        $content = str_repeat('abcdefgh', 65535);
        $request = $connection->newRequest(array(
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen($content)
        ), $content);

        $connection->sendRequest($request);
        $response = $connection->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->getContent());

        list($server) = unserialize($body);

        $this->assertEquals(strlen($content), $server['CONTENT_LENGTH']);
    }


    public function testSendRequestWithOversizedParameters()
    {
        $client = new ConnectionFactory($this->socketFactory);
        $connection = $client->connect('localhost:9331');

        $params = [];
        for ($i = 1; $i < 4000; $i++) {
            $params["param$i"] = "value$i";
        }
        $request = $connection->newRequest(array(
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ) + $params, 'foo=bar');

        $connection->sendRequest($request);
        $response = $connection->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->getContent());

        list($server) = unserialize($body);

        $this->assertEquals(7, $server['CONTENT_LENGTH']);
    }

    public function testFpmGoesAway()
    {
        $client = new ConnectionFactory($this->socketFactory);
        $connection = $client->connect('localhost:9331');
        $request = $connection->newRequest(array(
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/sleep.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ), 'foo=bar');

        $connection->sendRequest($request);

        exec(sprintf('kill %d', file_get_contents(__DIR__ . '/tmp/php5-fpm.pid')));

        $this->setExpectedException('\Socket\Raw\Exception');
        $response = $connection->receiveResponse($request);
        $this->assertEquals('x2', substr($response->getContent(), -2));
    }
}
