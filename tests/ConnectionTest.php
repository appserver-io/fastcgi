<?php
namespace Crunch\FastCGI;

use Socket\Raw\Factory;
use Symfony\Component\Process\Process;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Process */
    private static $process;

    private $socketFactory;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $conf = __DIR__ . '/Resources/php-fpm.conf';
        self::$process = new Process(sprintf('exec `which php5-fpm` -n -y %s -p %s', $conf, __DIR__));
        self::$process->setWorkingDirectory(__DIR__ . '/Resource');

        self::startServer();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        if (self::$process && self::$process->isRunning()) {
            self::$process->stop(10);
        }
        unlink(__DIR__ . '/php5-fpm.log');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->socketFactory = \Phake::partialMock('\Socket\Raw\Factory');

        if (!self::$process->isRunning()) {
            self::startServer();
        }
    }

    private static function startServer ()
    {
        self::$process = self::$process->restart();

        // 200ms. Hopefully thats enough
        time_nanosleep(0, 200000000);
    }


    public function testDummy ()
    {
        $client = new ConnectionFactory($this->socketFactory);
        $connection = $client->connect('localhost:9331');
        $request = $connection->newRequest([
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ], 'foo=bar');

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
        $request = $connection->newRequest([
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/sleep.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ], 'foo=bar');

        $connection->sendRequest($request);

        if (self::$process && self::$process->isRunning()) {
            self::$process->stop(10);
            while (self::$process->isRunning());
        }

        $this->setExpectedException('\Socket\Raw\Exception');
        $response = $connection->receiveResponse($request);
        $this->assertEquals('x2', substr($response->getContent(), -2));
    }
}
