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

use Indigo\Queue\Connector\RabbitConnector;
use Indigo\Queue\Job;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;

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
class RabbitConnectorTest extends AbstractMQConnectorTest
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

        $this->connector = new RabbitConnector($amqp);

        if ($this->connector->isConnected() === false) {
            $this->markTestSkipped(
                'RabbitMQ connection not available.'
            );
        }

        $this->connector->clear('test');
        $this->connector->clear('test_clear');
        $this->connector->clear('test_count');
    }

    /**
     * @covers ::isConnected
     */
    public function testConnected()
    {
        $this->assertTrue($this->connector->isConnected());
    }

    /**
     * @covers       ::push
     * @dataProvider jobProvider
     */
    public function testPush(Job $job)
    {
        $this->assertNull($this->connector->push('test', $job));
    }

    /**
     * @covers       ::delayed
     * @dataProvider jobProvider
     */
    public function testDelayed(Job $job)
    {
        $this->assertNull($this->connector->delayed('test', 1, $job));
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty.
     */
    public function testEmptyPop()
    {
        $this->connector->pop('test');
    }

    /**
     * @covers       ::release
     * @dataProvider jobProvider
     */
    public function testReleaseDelayed(Job $job)
    {
        $this->connector->push('test', $job);

        $manager = $this->connector->pop('test');

        $this->assertTrue($this->connector->release($manager, 1));
    }

    /**
     * @covers ::getChannel
     * @covers ::regenerateChannel
     */
    public function testChannel()
    {
        $expected = $this->connector->getChannel();

        $this->assertInstanceOf('PhpAmqpLib\\Channel\\AMQPChannel', $expected);

        $actual = $this->connector->regenerateChannel();

        $this->assertInstanceOf('PhpAmqpLib\\Channel\\AMQPChannel', $actual);

        $this->assertEquals($expected, $actual);
    }
}
