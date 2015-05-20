<?php
namespace Crunch\FastCGI\Client;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \Crunch\FastCGI\Client\ClientFactory
 * @covers \Crunch\FastCGI\Client\ClientFactory
 */
class ClientFactoryTest extends TestCase
{
    /** @var ObjectProphecy */
    private $socketFactory;

    protected function setUp()
    {
        TestCase::setUp();

        $this->socketFactory = $this->prophesize('\Socket\Raw\Factory');
        $this->socketFactory->createClient(Argument::any())->willReturn($this->prophesize('\Socket\Raw\Socket'));
    }

    /**
     * @covers ::connect
     * @uses \Crunch\FastCGI\Client\Client::__construct
     * @uses \Crunch\FastCGI\Connection\Connection::__construct
     * @uses \Crunch\FastCGI\Connection\Connection::__destruct
     */
    public function testCreateClient()
    {
        $factory = new ClientFactory($this->socketFactory->reveal());


        $connection = $factory->connect('foobar');

        self::assertInstanceOf('\Crunch\FastCGI\Client\Client', $connection);
    }

    /**
     * @covers ::connect
     * @uses \Crunch\FastCGI\Client\Client::__construct
     * @uses \Crunch\FastCGI\Connection\Connection::__construct
     * @uses \Crunch\FastCGI\Connection\Connection::__destruct
     */
    public function testConnectCanHandleSchemelessUnixSocketPaths()
    {
        $factory = new ClientFactory($this->socketFactory->reveal());
        $factory->connect('/foo/bar');

        $this->socketFactory->createClient(Argument::exact('unix:///foo/bar'))->shouldHaveBeenCalled();
    }
}
