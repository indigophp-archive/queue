<?php

namespace Indigo\Queue\Job;

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
        $this->connector->push('test', $payload);

        $job = $this->connector->pop('test');

        if ($job instanceof RabbitJob) {
            $this->assertEquals(1, $job->attempts());
            $this->assertInstanceOf(
                'PhpAmqpLib\\Message\\AMQPMessage',
                $job->getMessage()
            );

            $this->assertTrue($job->delete());
        } else {
            $this->assertNull($job);
        }

        return $job;
    }
}
