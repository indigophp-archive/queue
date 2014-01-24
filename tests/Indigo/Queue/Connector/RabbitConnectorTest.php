<?php

namespace Indigo\Queue\Connector;

use Indigo\Queue\Job\BeanstalkdJob;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitConnectorTest extends ConnectorTest
{
    public function setUp()
    {
        $host = isset($GLOBALS['rabbit_host']) ? $GLOBALS['rabbit_host'] : 'localhost';
        $port = isset($GLOBALS['rabbit_port']) ? $GLOBALS['rabbit_port'] : 5672;
        $user = isset($GLOBALS['rabbit_user']) ? $GLOBALS['rabbit_user'] : 'guest';
        $pass = isset($GLOBALS['rabbit_pass']) ? $GLOBALS['rabbit_pass'] : 'guest';

        $amqp = new AMQPStreamConnection($host, $port, $user, $pass);

        $this->connector = new RabbitConnector($amqp);

        if (!$this->connector->isConnected()) {
            $this->markTestSkipped(
              'RabbitMQ connection not available.'
            );
        }
    }

    public function testConnection()
    {
        $this->assertTrue($this->connector->isConnected());
    }

    public function testAMQPInstance()
    {
        $amqp = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $connector = new RabbitConnector($amqp);

        $this->assertTrue($connector->isConnected());
    }

    public function testAMQP()
    {
        $amqp = $this->connector->getAMQP();

        $this->assertInstanceOf('PhpAmqpLib\\Connection\\AbstractConnection', $amqp);

        $this->assertInstanceOf(
            'Indigo\\Queue\\Connector\\RabbitConnector',
            $this->connector->setAMQP($amqp)
        );
    }

    public function testPush()
    {
        $payload = array(
            'job' => 'Job',
            'data' => array(),
            'queue' => 'test'
        );

        $payload = $this->connector->push($payload);
        $this->assertNull($payload);
    }

    public function testDelayed()
    {
        $payload = array(
            'job' => 'Job',
            'data' => array(),
            'queue' => 'test'
        );

        $payload = $this->connector->delayed(100, $payload);
        $this->assertNull($payload);
    }

    public function testPop()
    {
        if ($job = $this->connector->pop('test')) {
            $this->assertInstanceOf(
                'Indigo\\Queue\\Job\\RabbitJob',
                $job
            );
        } else {
            $this->assertNull($job);
        }

        $this->assertNull($this->connector->pop('null', 0.25));
    }

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
