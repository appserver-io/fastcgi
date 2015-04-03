<?php
namespace Crunch\FastCGI;

use Assert\AssertionFailedException;
use Socket\Raw\Exception as SocketException;
use Socket\Raw\Factory;

/**
 * @coversNothing
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /** @var Factory */
    private $socketFactory;

    private $fpmExists = true;

    protected function setUp()
    {
        parent::setUp();

        $this->socketFactory = new Factory();

        $conf = __DIR__ . '/Resources/php-fpm.conf';
        // start fpm daemon
        $output = $exitCode = null;
        exec("`which php-fpm` -n -y $conf -p " . __DIR__ . '/tmp 2>&1 1>/dev/null', $output, $exitCode);
        $this->fpmExists = ($exitCode == 0);

        // wait until pid file is generate
        while ($this->fpmExists && !is_file(__DIR__ . '/tmp/php5-fpm.pid')) {
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
        if (!$this->fpmExists) {
            self::markTestSkipped('php-fpm not found on this system');
            return;
        }


        $connectionFactory = new ConnectionFactory($this->socketFactory);
        $client = new Client('localhost:9331', $connectionFactory);

        $request = $client->newRequest(array(
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ), 'foo=bar');

        $client->sendRequest($request);
        $response = $client->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->getContent());

        list($server) = unserialize($body);

        self::assertEquals(7, $server['CONTENT_LENGTH']);
    }


    public function testSendSimpleRequestWithOversizedPayload()
    {
        if (!$this->fpmExists) {
            self::markTestSkipped('php-fpm not found on this system');
            return;
        }

        $connectionFactory = new ConnectionFactory($this->socketFactory);
        $client = new Client('localhost:9331', $connectionFactory);


        $content = str_repeat('abcdefgh', 65535);
        $request = $client->newRequest(array(
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen($content)
        ), $content);

        $client->sendRequest($request);
        $response = $client->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->getContent());

        list($server) = unserialize($body);

        self::assertEquals(strlen($content), $server['CONTENT_LENGTH']);
    }


    public function testSendRequestWithOversizedParameters()
    {
        if (!$this->fpmExists) {
            self::markTestSkipped('php-fpm not found on this system');
            return;
        }

        $connectionFactory = new ConnectionFactory($this->socketFactory);
        $client = new Client('localhost:9331', $connectionFactory);

        $params = [];
        for ($i = 1; $i < 4000; $i++) {
            $params["param$i"] = "value$i";
        }
        $request = $client->newRequest(array(
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ) + $params, 'foo=bar');

        $client->sendRequest($request);
        $response = $client->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->getContent());

        list($server) = unserialize($body);

        self::assertEquals(7, $server['CONTENT_LENGTH']);
    }

    public function testFpmGoesAway()
    {
        if (!$this->fpmExists) {
            self::markTestSkipped('php-fpm not found on this system');
            return;
        }

        $connectionFactory = new ConnectionFactory($this->socketFactory);
        $client = new Client('localhost:9331', $connectionFactory);


        $request = $client->newRequest(array(
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/sleep.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ), 'foo=bar');

        time_nanosleep(0, 50000);
            $client->sendRequest($request);

        exec(sprintf('kill %d', file_get_contents(__DIR__ . '/tmp/php5-fpm.pid')));

        try {
            $client->receiveResponse($request);
        } catch (AssertionFailedException $e) {
            // Also possible: The server dies while the connection tries to
            // read the content and therefore only partials arrive
            self::assertTrue(true);
            return;
        } catch (SocketException $e) {
            self::assertTrue(true);
            return;
        }
        self::assertTrue(false, 'None of the expected exceptions were thrown');
    }
}
