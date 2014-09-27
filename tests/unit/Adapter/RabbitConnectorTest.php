<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Unit;

use Indigo\Queue\Adapter\RabbitAdapter;

/**
 * Tests for RabbitAdapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Adapter\RabbitAdapter
 * @group              Queue
 * @group              Adapter
 * @group              Rabbit
 */
class RabbitAdapterTest extends AbstractAdapterTest
{
    /**
     * AMQP mock
     *
     * @var PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected $amqp;

    public function _before()
    {
        $this->amqp = \Mockery::mock('PhpAmqpLib\\Connection\\AMQPStreamConnection');

        $this->amqp->shouldReceive('channel')
            ->andReturn(null);

        $this->adapter = new RabbitAdapter($this->amqp);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $adapter = new RabbitAdapter($this->amqp);

        $this->assertSame($this->amqp, $adapter->getAMQP());
        $this->assertTrue($this->adapter->isPersistent());
    }

    /**
     * @covers ::isPersistent
     */
    public function testPersistent()
    {
        $this->assertTrue($this->adapter->isPersistent());
    }

    /**
     * @covers ::getAMQP
     * @covers ::setAMQP
     */
    public function testAMQP()
    {
        $amqp = $this->adapter->getAMQP();

        $this->assertSame($this->adapter, $this->adapter->setAMQP($amqp));
        $this->assertSame($amqp, $this->adapter->getAMQP());
    }
}
