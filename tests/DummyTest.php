<?php
namespace Crunch\FastCGI;

use Symfony\Component\Process\Process;

class DummyTest extends \PHPUnit_Framework_TestCase
{
    /** @var Process */
    private $process;
    protected function setUp()
    {
        $conf = __DIR__ . '/Resources/php-fpm.conf';
        $this->process = new Process(sprintf('exec `which php5-fpm` -F -n -y %s -p %s', $conf, __DIR__));
        $this->process->setWorkingDirectory(__DIR__ . '/Resource');
        $this->process->start(function ($type, $message) {
            var_dump($type);
            var_dump($message);
        });
        parent::setUp();
        sleep(1);

        $this->process->getIncrementalErrorOutput();
        $this->process->getIncrementalOutput();
    }

    protected function tearDown()
    {
        if ($this->process) {
            $this->process->getIncrementalErrorOutput();
            $this->process->getIncrementalOutput();
        }
        if ($this->process && $this->process->isRunning()) {
            $this->process->stop(10);
            $this->process = null;
        }

        parent::tearDown();
    }


    public function testDummy ()
    {
        $client = new Client('localhost', 9000);
        $connection = $client->connect();
        $request = $connection->newRequest(array(
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ), 'foo=bar');

        $connection->sendRequest($request);
        $response = $connection->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->content);

        list($server) = unserialize($body);

        $this->assertEquals(7, $server['CONTENT_LENGTH']);
    }

    public function testFpmGoesAway()
    {
        $client = new Client('localhost', 9000);
        $connection = $client->connect();
        $request = $connection->newRequest(array(
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/sleep.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ), 'foo=bar');

        $connection->sendRequest($request);

        if ($this->process && $this->process->isRunning()) {
            $this->process->stop(10);
            while ($this->process->isRunning());
            $this->process = null;
        }

        $this->setExpectedException('\Crunch\FastCGI\ConnectionException');
        $response = $connection->receiveResponse($request);
        $this->assertEquals('x2', substr($response->content, -2));
    }
}
