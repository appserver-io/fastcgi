<?php
namespace Crunch\FastCGI;

use Assert\AssertionFailedException;
use Crunch\FastCGI\Client\Client;
use Crunch\FastCGI\Client\Factory as ClientFactory;
use Crunch\FastCGI\Protocol\RequestParameters;
use Crunch\FastCGI\Protocol\Response;
use Crunch\FastCGI\ReaderWriter\StringReader;
use React\Dns\Resolver\Factory as ResolverFactory;
use React\EventLoop\Factory as LoopFactory;
use React\SocketClient\Connector as SocketConnector;

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

        if ($this->isFpmRunning()) {
            return;
        }

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

    public static function tearDownAfterClass()
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

    private function expectPHPFPMRunning()
    {
        if (!$this->isFpmRunning()) {
            self::markTestSkipped('PHP FastCGI-server not running. You can set PHPFPM_BIN to point to the right location of the binary.');
        }
    }

    private function isFpmRunning()
    {
        if (!file_exists(__DIR__ . '/tmp/php5-fpm.pid')) {
            return false;
        }

        $output = $status = null;
        exec('exec kill -0 ' . file_get_contents(__DIR__ . '/tmp/php5-fpm.pid'), $output, $status);

        return $status === 0;
    }

    /**
     * @medium
     */
    public function testSendSimpleRequest()
    {
        $this->expectPHPFPMRunning();

        $loop = LoopFactory::create();

        $dnsResolverFactory = new ResolverFactory();
        $dns = $dnsResolverFactory->createCached('0.0.0.0', $loop);

        $connector = new SocketConnector($loop, $dns);

        $factory = new ClientFactory($loop, $connector);


        $factory->createClient('127.0.0.1', 9331)->then(function (Client $client) {
            $request = $client->newRequest(new RequestParameters([
                'REQUEST_METHOD'  => 'POST',
                'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
                'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
                'CONTENT_LENGTH'  => strlen('foo=bar')
            ]), new StringReader('foo=bar'));

            $client->sendRequest($request)->then(function (Response $response) use ($client) {
                $client->close();

                list(, $body) = explode("\r\n\r\n", $response->getContent()->read());
                list($server) = unserialize($body);
                self::assertEquals(7, $server['CONTENT_LENGTH']);

                return $client;
            });

            return $client;
        });


        $loop->run();
    }

    /**
     * @medium
     */
    public function testSendSimpleGetRequest()
    {
        $this->expectPHPFPMRunning();

        $loop = LoopFactory::create();

        $dnsResolverFactory = new ResolverFactory();
        $dns = $dnsResolverFactory->createCached('0.0.0.0', $loop);

        $connector = new SocketConnector($loop, $dns);

        $factory = new ClientFactory($loop, $connector);


        $factory->createClient('127.0.0.1', 9331)->then(function (Client $client) {
            $request = $client->newRequest(new RequestParameters([
                'REQUEST_METHOD'  => 'GET',
                'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
                'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
                'CONTENT_LENGTH'  => 0
            ]));

            $client->sendRequest($request)->then(function (Response $response) use ($client) {
                $client->close();

                list($header, $body) = explode("\r\n\r\n", $response->getContent()->read());
                list($server) = unserialize($body);
                self::assertEquals(0, $server['CONTENT_LENGTH']);
            });

            return $client;
        });

        $loop->run();
    }

    /**
     * @medium
     */
    public function testSendSimpleRequestWithOversizedPayload()
    {
        $this->expectPHPFPMRunning();

        $loop = LoopFactory::create();

        $dnsResolverFactory = new ResolverFactory();
        $dns = $dnsResolverFactory->createCached('0.0.0.0', $loop);

        $connector = new SocketConnector($loop, $dns);

        $factory = new ClientFactory($loop, $connector);


        $factory->createClient('127.0.0.1', 9331)->then(function (Client $client) {
            $content = str_repeat('abcdefgh', 65535);
            $request = $client->newRequest(new RequestParameters([
                'REQUEST_METHOD'  => 'POST',
                'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
                'CONTENT_LENGTH'  => strlen($content)
            ]), new StringReader($content));

            $client->sendRequest($request)->then(function (Response $response) use ($client, $content) {
                $client->close();

                list($header, $body) = explode("\r\n\r\n", $response->getContent()->read());

                list($server) = unserialize($body);

                self::assertEquals(strlen($content), $server['CONTENT_LENGTH']);
            });

            return $client;
        });

        $loop->run();
    }

    /**
     * @medium
     */
    public function testSendRequestWithOversizedParameters()
    {
        $this->expectPHPFPMRunning();

        $loop = LoopFactory::create();

        $dnsResolverFactory = new ResolverFactory();
        $dns = $dnsResolverFactory->createCached('0.0.0.0', $loop);

        $connector = new SocketConnector($loop, $dns);

        $factory = new ClientFactory($loop, $connector);


        $factory->createClient('127.0.0.1', 9331)->then(function (Client $client) {
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
            $request = $client->newRequest(new RequestParameters($params), new StringReader('foo=bar'));

            $client->sendRequest($request)->then(function (Response $response) use ($client) {
                $client->close();

                list($header, $body) = explode("\r\n\r\n", $response->getContent()->read());
                list($server) = unserialize($body);
                self::assertEquals(7, $server['CONTENT_LENGTH']);
            });

            return $client;
        });

        $loop->run();
    }
}
