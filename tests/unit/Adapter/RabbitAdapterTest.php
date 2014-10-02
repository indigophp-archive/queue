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

    /**
     * AMQP Channel mock
     * @var PhpAmqpLib\Channel\AMQPChannel
     */
    protected $chanel;

    public function _before()
    {
        $this->amqp = \Mockery::mock('PhpAmqpLib\\Connection\\AMQPStreamConnection');

        $this->channel = \Mockery::mock('PhpAmqpLib\\Channel\\AMQPChannel');

        $this->channel->shouldReceive('queue_declare')
            ->shouldReceive('exchange_declare')
            ->shouldReceive('close');

        $this->channel->is_open = true;

        $this->amqp->shouldReceive('channel')
            ->andReturn($this->channel);

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
     * @covers ::isConnected
     */
    public function testConnection()
    {
        $this->assertFalse($this->adapter->isConnected());
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
        $amqp = \Mockery::mock('PhpAmqpLib\\Connection\\AMQPStreamConnection');

        $this->assertSame($this->adapter, $this->adapter->setAMQP($amqp));
        $this->assertSame($amqp, $this->adapter->getAMQP());
    }

    /**
     * @covers ::getChannel
     * @covers ::isConnected
     */
    public function testGetChannel()
    {
        $channel = $this->adapter->getChannel('test');

        $this->assertInstanceOf('PhpAmqpLib\\Channel\\AMQPChannel', $channel);
        $this->assertTrue($this->adapter->isConnected());

        $this->assertSame($channel, $this->adapter->getChannel('test'));
    }

    /**
     * @covers ::push
     */
    public function testPush()
    {
        $this->channel
            ->shouldReceive('basic_publish')
            ->andReturn(true);

        parent::testPush();
    }

    /**
     * @covers ::pop
     */
    public function testPop()
    {
        $message = \Mockery::mock('PhpAmqpLib\\Message\\AMQPMessage');
        $message->body = json_encode([]);
        $message->delivery_info = ['delivery_tag' => 1];

        $this->channel
            ->shouldReceive('basic_get')
            ->andReturn($message);

        parent::testPop();
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty
     */
    public function testEmptyPop()
    {
        $this->channel
            ->shouldReceive('basic_get')
            ->andReturn(null);

        parent::testEmptyPop();
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $this->channel->shouldReceive('basic_ack');

        parent::testDelete();
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $this->channel->shouldReceive('queue_purge');

        parent::testClear();
    }

    /**
     * @covers ::release
     */
    public function testRelease()
    {
        $this->channel->shouldReceive('basic_ack');

        parent::testRelease();
    }
}
