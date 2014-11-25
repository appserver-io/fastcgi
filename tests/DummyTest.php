<?php
namespace Crunch\FastCGI;

class DummyTest extends \PHPUnit_Framework_TestCase
{
    /** @var Process */
    private $process;


    protected function setUp()
    {
        // init directory vars
        $conf = __DIR__ . '/Resources/php-fpm.conf';
        $pidFile = __DIR__ . '/php5-fpm.pid';

        // start fpm daemon
        exec(sprintf('/usr/sbin/php5-fpm -n -y %s -p %s', $conf, __DIR__));

        $waitms = 0;
        // wait until pid file is generate
        while(!is_file($pidFile)) {
            usleep(100000);
            $waitms += 100000;
            // if 3 secs over we will break here
            if ($waitms * 0.000001 > 2.99) {
                $this->fail('Can not start fpm daemon');
                break;
            }
        }

        // store pid for later process killing
        $this->pid = file_get_contents($pidFile);

        parent::setUp();
    }

    protected function tearDown()
    {
        // kill fpm daemon if pid exists
        if ($this->pid) {
            exec(sprintf('kill %d', $this->pid));
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
            'Foo'             => 'Bar', 'GATEWAY_INTERFACE' => 'FastCGI/1.0',
            'REQUEST_METHOD'  => 'POST',
            'SCRIPT_FILENAME' => __DIR__ . '/Resources/scripts/sleep.php',
            'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'  => strlen('foo=bar')
        ), 'foo=bar');

        $connection->sendRequest($request);

        // kill fpm daemon
        exec(sprintf('kill %d', $this->pid));
        $this->pid = null;

        // Try to receive a response, it will either run indefinetly (bad!) or fail as the BE stopped
        $connection->receiveResponse($request);
    }
}
