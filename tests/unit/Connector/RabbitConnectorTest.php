<?php

namespace Test\Unit;

use Indigo\Queue\Connector\RabbitConnector;

/**
 * Tests for RabbitConnector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Connector\RabbitConnector
 * @group              Queue
 * @group              Connector
 * @group              Rabbit
 */
class RabbitConnectorTest extends AbstractConnectorTest
{
    /**
     * AMQP mock
     *
     * @var AMQPStreamConnection
     */
    protected $amqp;

    public function _before()
    {
        $this->amqp = \Mockery::mock('PhpAmqpLib\\Connection\\AMQPStreamConnection');

        $this->amqp->shouldReceive('channel')
            ->andReturn(null);

        $this->connector = new RabbitConnector($this->amqp);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $connector = new RabbitConnector($this->amqp);

        $this->assertSame($this->amqp, $connector->getAMQP());
        $this->assertTrue($this->connector->isPersistent());
    }

    /**
     * @covers ::isPersistent
     */
    public function testPersistent()
    {
        $this->assertTrue($this->connector->isPersistent());
    }

    /**
     * @covers ::getAMQP
     * @covers ::setAMQP
     */
    public function testAMQP()
    {
        $amqp = $this->connector->getAMQP();

        $this->assertSame($this->connector, $this->connector->setAMQP($amqp));
        $this->assertSame($amqp, $this->connector->getAMQP());
    }
}
