<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Functional;

use Indigo\Queue\Adapter\RabbitAdapter;
use Indigo\Queue\Job;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;

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
class RabbitAdapterTest extends AbstractMQAdapterTest
{
    public function _before()
    {
        $host = $GLOBALS['rabbit_host'];
        $port = $GLOBALS['rabbit_port'];
        $user = $GLOBALS['rabbit_user'];
        $pass = $GLOBALS['rabbit_pass'];
        $vhost = $GLOBALS['rabbit_vhost'];

        try {
            $amqp = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
        } catch (AMQPRuntimeException $e) {
            $this->markTestSkipped(
                'Rabbit credentials are not available.'
            );
        }

        $this->adapter = new RabbitAdapter($amqp);

        if ($this->adapter->isConnected() === false) {
            $this->markTestSkipped(
                'RabbitMQ connection not available.'
            );
        }

        $this->adapter->clear('test');
        $this->adapter->clear('test_clear');
        $this->adapter->clear('test_count');
    }

    /**
     * @covers ::isConnected
     */
    public function testConnected()
    {
        $this->assertTrue($this->adapter->isConnected());
    }

    /**
     * @covers       ::push
     * @dataProvider jobProvider
     */
    public function testPush(Job $job)
    {
        $this->assertNull($this->adapter->push('test', $job));
    }

    /**
     * @covers       ::delayed
     * @dataProvider jobProvider
     */
    public function testDelayed(Job $job)
    {
        $this->assertNull($this->adapter->delayed('test', 1, $job));
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty.
     */
    public function testEmptyPop()
    {
        $this->adapter->pop('test');
    }

    /**
     * @covers       ::release
     * @dataProvider jobProvider
     */
    public function testReleaseDelayed(Job $job)
    {
        $this->adapter->push('test', $job);

        $manager = $this->adapter->pop('test');

        $this->assertTrue($this->adapter->release($manager, 1));
    }

    /**
     * @covers ::getChannel
     * @covers ::regenerateChannel
     */
    public function testChannel()
    {
        $expected = $this->adapter->getChannel();

        $this->assertInstanceOf('PhpAmqpLib\\Channel\\AMQPChannel', $expected);

        $actual = $this->adapter->regenerateChannel();

        $this->assertInstanceOf('PhpAmqpLib\\Channel\\AMQPChannel', $actual);

        $this->assertEquals($expected, $actual);
    }
}
