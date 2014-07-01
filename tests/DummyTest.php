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
        $this->process = new Process(sprintf('`which php5-fpm` -F -n -y %s -p %s', $conf, __DIR__));
        $this->process->setWorkingDirectory(__DIR__ . '/Resources');
        $this->process->start();
        parent::setUp();

        // Sleep for some time to allow the process to start listening
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
            'Foo' => 'Bar',
            'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD' => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/echo.php',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => strlen('foo=bar')
        ), 'foo=bar');

        $connection->sendRequest($request);
        $response = $connection->receiveResponse($request);

        list($header, $body) = explode("\r\n\r\n", $response->content);

        list($server) = unserialize($body);

        $this->assertEquals(7, $server['CONTENT_LENGTH']);
    }

    /**
     * @medium
     */
    public function testFpmGoesAway()
    {
        // We expect this to fail with a ConnectionException
        $this->setExpectedException('\Crunch\FastCGI\ConnectionException');

        // Get a MockClient instead of a real one so we can influence the connection's behaviour
        $client = new Client('localhost', 9000);
        $connection = $client->connect();
        $request = $connection->newRequest(array(
            'Foo' => 'Bar',
            'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD' => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/sleep.php',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => strlen('foo=bar')
        ), 'foo=bar');

        $connection->sendRequest($request);

        // If the process is running we will stop it and destroy the reference
        if ($this->process && $this->process->isRunning()) {
            $this->process->stop(2);
            $this->process = null;
        }

        // Try to receive a response, it will either run indefinetly (bad!) or fail as the BE stopped
        $connection->receiveResponse($request);
    }
}
