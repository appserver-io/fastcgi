<?php
namespace Crunch\FastCGI;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \Crunch\FastCGI\ConnectionFactory
 * @covers \Crunch\FastCGI\ConnectionFactory
 */
class ConnectionFactoryTest extends TestCase
{
    /** @var ObjectProphecy */
    private $socketFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->socketFactory = $this->prophesize('\Socket\Raw\Factory');
        $this->socketFactory->createClient(Argument::any())->willReturn($this->prophesize('\Socket\Raw\Socket'));
    }

    /**
     * @covers ::connect
     * @uses \Crunch\FastCGI\Connection::__construct
     * @uses \Crunch\FastCGI\Connection::__destruct
     */
    public function testCreateConnection()
    {
        $factory = new ConnectionFactory($this->socketFactory->reveal());


        $connection = $factory->connect('foobar');

        self::assertInstanceOf('\Crunch\FastCGI\Connection', $connection);
    }
}
