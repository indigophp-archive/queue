<?php

namespace Indigo\Queue\Test\Job;

use Indigo\Queue\Job\RabbitJob;
use Indigo\Queue\Connector\RabbitConnector;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitJobTest extends JobTest
{
    public function setUp()
    {
        $host = isset($GLOBALS['rabbit_host']) ? $GLOBALS['rabbit_host'] : 'localhost';
        $port = isset($GLOBALS['rabbit_port']) ? $GLOBALS['rabbit_port'] : 5672;
        $user = isset($GLOBALS['rabbit_user']) ? $GLOBALS['rabbit_user'] : 'guest';
        $pass = isset($GLOBALS['rabbit_pass']) ? $GLOBALS['rabbit_pass'] : 'guest';
        $vhost = isset($GLOBALS['rabbit_vhost']) ? $GLOBALS['rabbit_vhost'] : '/';

        $amqp = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);

        $this->connector = new RabbitConnector($amqp);

        if (!$this->connector->isConnected()) {
            $this->markTestSkipped(
              'RabbitMQ connection not available.'
            );
        }
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testJob($payload, $return)
    {
        $queue = 'test_' . uniqid();
        $this->connector->push($queue, $payload);

        $job = $this->connector->pop($queue);

        if ($job instanceof RabbitJob) {
            $this->assertEquals(1, $job->attempts());
            $this->assertInstanceOf(
                'PhpAmqpLib\\Message\\AMQPMessage',
                $job->getMessage()
            );
        } else {
            $this->assertNull($job);
        }

        return $job;
    }
}
