<?php
namespace Crunch\FastCGI;

use Assert\AssertionFailedException;
use Socket\Raw\Exception as SocketException;
use Socket\Raw\Factory as SocketFactory;

/**
 * Integration test testing the client against a real FastCGI-server (php5-fpm)
 *
 * @coversNothing
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    private $fpmExists = true;

    protected function setUp()
    {
        parent::setUp();

        $conf = __DIR__ . '/Resources/php-fpm.conf';
        // start fpm daemon
        $output = $exitCode = null;
        $binary = getenv('PHPFPM_BIN') ?: '/usr/sbin/php5-fpm';
        exec("$binary -n -y $conf -p " . __DIR__ . '/tmp 2>&1 1>/dev/null', $output, $exitCode);
        $this->fpmExists = ($exitCode == 0);

        // wait until pid file is generate
        while ($this->fpmExists && !is_file(__DIR__ . '/tmp/php5-fpm.pid')) {
            usleep(100000);
        }
    }

    public function tearDown()
    {
        parent::tearDown();

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

    private function expectPHPFPMRunning()
    {
        $x = file_exists(__DIR__ . '/tmp/php5-fpm.pid');
        if (!$x) {
            self::markTestSkipped('PHP FastCGI-server not running. You can set PHPFPM_BIN to point to the right location of the binary.');
        }
        $output = $status = null;
        exec('exec kill -0 ' . file_get_contents(__DIR__ . '/tmp/php5-fpm.pid'), $output, $status);
        if ($status) {
            self::markTestSkipped('PHP FastCGI-server not running. You can set PHPFPM_BIN to point to the right location of the binary.');
        }
    }


    public function testSendSimpleRequest()
    {
        $this->expectPHPFPMRunning();


        $socketFactory = new SocketFactory();
        $clientFactory = new ClientFactory($socketFactory);
        $client = $clientFactory->connect('localhost:9331');

        $request = $client->newRequest(new RequestParameters([
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ]), 'foo=bar');

        $client->sendRequest($request);

        $response = $client->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->getContent());

        list($server) = unserialize($body);

        self::assertEquals(7, $server['CONTENT_LENGTH']);
    }


    public function testSendSimpleGetRequest()
    {
        $this->expectPHPFPMRunning();


        $socketFactory = new SocketFactory();
        $clientFactory = new ClientFactory($socketFactory);
        $client = $clientFactory->connect('localhost:9331');

        $request = $client->newRequest(new RequestParameters([
            'REQUEST_METHOD'  => 'GET',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => 0
        ]), '');

        $client->sendRequest($request);

        $response = $client->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->getContent());

        list($server) = unserialize($body);

        self::assertEquals(0, $server['CONTENT_LENGTH']);
    }


    public function testSendSimpleRequestWithOversizedPayload()
    {
        $this->expectPHPFPMRunning();


        $socketFactory = new SocketFactory();
        $clientFactory = new ClientFactory($socketFactory);
        $client = $clientFactory->connect('localhost:9331');


        $content = str_repeat('abcdefgh', 65535);
        $request = $client->newRequest(new RequestParameters([
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_LENGTH'  => strlen($content)
        ]), $content);

        $client->sendRequest($request);
        $response = $client->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->getContent());

        list($server) = unserialize($body);

        self::assertEquals(strlen($content), $server['CONTENT_LENGTH']);
    }


    public function testSendRequestWithOversizedParameters()
    {
        $this->expectPHPFPMRunning();


        $socketFactory = new SocketFactory();
        $clientFactory = new ClientFactory($socketFactory);
        $client = $clientFactory->connect('localhost:9331');

        $params = [
            'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ];
        for ($i = 1; $i < 4000; $i++) {
            $params["param$i"] = "value$i";
        }
        $request = $client->newRequest(new RequestParameters($params), 'foo=bar');

        $client->sendRequest($request);
        $response = $client->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->getContent());

        list($server) = unserialize($body);

        self::assertEquals(7, $server['CONTENT_LENGTH']);
    }

    public function testFpmGoesAway()
    {
        $this->expectPHPFPMRunning();


        $socketFactory = new SocketFactory();
        $clientFactory = new ClientFactory($socketFactory);
        $client = $clientFactory->connect('localhost:9331');


        $request = $client->newRequest(new RequestParameters([
            'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/sleep.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ]), 'foo=bar');

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
