<?php

namespace Indigo\Queue\Test\Connector;

use Indigo\Queue\Connector\RabbitConnector;
use Indigo\Queue\Job\RabbitJob;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;

/**
 * Tests for Rabbit Connector
 *
 * @author  Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass  Indigo\Queue\Connector\RabbitConnector
 */
class RabbitConnectorTest extends ConnectorTest
{
    public function setUp()
    {
        $host = isset($GLOBALS['rabbit_host']) ? $GLOBALS['rabbit_host'] : 'localhost';
        $port = isset($GLOBALS['rabbit_port']) ? $GLOBALS['rabbit_port'] : 5672;
        $user = isset($GLOBALS['rabbit_user']) ? $GLOBALS['rabbit_user'] : 'guest';
        $pass = isset($GLOBALS['rabbit_pass']) ? $GLOBALS['rabbit_pass'] : 'guest';
        $vhost = isset($GLOBALS['rabbit_vhost']) ? $GLOBALS['rabbit_vhost'] : '/';

        try {
            $amqp = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
        } catch (AMQPRuntimeException $e) {
            $this->markTestSkipped(
                'RabbitMQ connection not available.'
            );
        }

        $this->connector = new RabbitConnector($amqp);

        if (!$this->connector->isConnected()) {
            $this->markTestSkipped(
                'RabbitMQ connection not available.'
            );
        }
    }

    /**
     * @covers ::isPersistent
     * @group  Queue
     */
    public function testPersistent()
    {
        $this->assertTrue($this->connector->isPersistent());
    }

    /**
     * @covers ::isConnected
     * @group  Queue
     */
    public function testAMQPInstance()
    {
        $amqp = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $connector = new RabbitConnector($amqp);

        $this->assertTrue($connector->isConnected());
    }

    /**
     * @covers ::getAMQP
     * @covers ::setAMQP
     * @group  Queue
     */
    public function testAMQP()
    {
        $amqp = $this->connector->getAMQP();

        $this->assertInstanceOf('PhpAmqpLib\\Connection\\AbstractConnection', $amqp);

        $this->assertEquals(
            $this->connector,
            $this->connector->setAMQP($amqp)
        );
    }

    /**
     * @covers       ::push
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testPush($payload)
    {
        $payload = $this->connector->push('test', $payload);
        $this->assertNull($payload);
    }

    /**
     * @covers       ::delayed
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testDelayed($payload)
    {
        $payload = $this->connector->delayed('test', 1, $payload);
        $this->assertNull($payload);
    }

    /**
     * @covers       ::pop
     * @covers       ::delete
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testPop($payload)
    {
        $queue = 'test_pop_' . uniqid();
        $this->connector->push($queue, $payload);

        if ($job = $this->connector->pop($queue)) {
            $this->assertInstanceOf(
                'Indigo\\Queue\\Job\\RabbitJob',
                $job
            );

            $this->assertEquals($payload, $job->getPayload());
            $this->assertTrue($this->connector->delete($job));
        } else {
            $this->assertNull($job);
        }

        $this->assertNull($this->connector->pop('null', 0.25));
    }

    /**
     * @covers       ::pop
     * @covers       ::release
     * @dataProvider payloadProvider
     * @group        Queue
     */
    public function testRelease($payload)
    {
        $queue = 'test_release_' . uniqid();
        $this->connector->push($queue, $payload);

        if ($job = $this->connector->pop($queue)) {
            $this->assertInstanceOf(
                'Indigo\\Queue\\Job\\RabbitJob',
                $job
            );

            if ($payload['job'] == 'Job@runThis') {
                $this->assertTrue($this->connector->release($job));
            } else {
                $this->assertTrue($this->connector->release($job, 1));
            }
        } else {
            $this->assertNull($job);
        }
    }

    /**
     * @covers ::getChannel
     * @covers ::regenerateChannel
     * @group  Queue
     */
    public function testChannel()
    {
        $channel1 = $this->connector->getChannel();
        $this->assertInstanceOf(
            'PhpAmqpLib\\Channel\\AMQPChannel',
            $channel1
        );

        $channel2 = $this->connector->regenerateChannel();
        $this->assertInstanceOf(
            'PhpAmqpLib\\Channel\\AMQPChannel',
            $channel2
        );

        $this->assertEquals($channel1, $channel2);
    }
}
