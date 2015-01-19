<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;
use Socket\Raw\Factory as SocketFactory;
use Phake_IMock as Mock;
use Phake as p;

/**
 * @coversDefaultClass \Crunch\FastCGI\ConnectionFactory
 * @covers \Crunch\FastCGI\ConnectionFactory
 */
class ConnectionFactoryTest extends TestCase
{
    /**
     * @var SocketFactory|Mock
     */
    private $socketFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->socketFactory = p::mock('\Socket\Raw\Factory');

        $socket = p::mock('\Socket\Raw\Socket');

        p::when($this->socketFactory)->createClient('foobar')->thenReturn($socket);
    }

    /**
     * @covers ::connect
     * @uses \Crunch\FastCGI\Connection::__construct
     * @uses \Crunch\FastCGI\Connection::__destruct
     */
    public function testCreateConnection()
    {
        $factory = new ConnectionFactory($this->socketFactory);

        $connection = $factory->connect('foobar');

        $this->assertInstanceOf('\Crunch\FastCGI\Connection', $connection);
    }
}
