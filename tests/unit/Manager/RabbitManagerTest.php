<?php

namespace Test\Unit;

use Indigo\Queue\Manager\RabbitManager;

/**
 * Tests for RabbitManager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Manager\RabbitManager
 * @group              Queue
 * @group              Manager
 * @group              Rabbit
 */
class RabbitManagerTest extends AbstractManagerTest
{
    /**
     * AMQP Message
     *
     * @var AMQPMessage
     */
    protected $message;

    public function _before()
    {
        $this->message = \Mockery::mock('PhpAmqpLib\\Message\\AMQPMessage');

        $this->connector = \Mockery::mock('Indigo\\Queue\\Connector\\RabbitConnector');

        $this->connector->shouldReceive('regenerateChannel')
            ->andReturn(null);

        $this->manager = new RabbitManager('test', $this->message, $this->connector);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $manager = new RabbitManager('test', $this->message, $this->connector);

        $this->assertSame($this->message, $this->manager->getMessage());
    }

    /**
     * @covers ::attempts
     */
    public function testAttempts()
    {
        $this->assertEquals(1, $this->manager->attempts());
    }

    /**
     * @covers ::getMessage
     */
    public function testGetMessage()
    {
        $this->assertSame($this->message, $this->manager->getMessage());
    }

    /**
     * @covers ::getChannel
     */
    public function testGetChannel()
    {
        $this->assertNull($this->manager->getChannel());
    }
}
