<?php

namespace Indigo\Queue\Job;

use Jeremeamia\SuperClosure\SerializableClosure;

class RabbitJobTest extends JobTest
{
    public function setUp()
    {
        $msg = \Mockery::mock('PhpAmqpLib\\Message\\AMQPMessage');
        $msg->body = json_encode(array(
                    'job' => 'Job',
                    'data' => array(
                        'test',
                        'test2'
                    )
                ));
        $msg->delivery_info = array('delivery_tag' => 1);

        $this->connector = \Mockery::mock(
            'Indigo\\Queue\\Connector\\RabbitConnector',
            function ($mock) {
                $mock->shouldReceive('regenerateChannel')
                    ->andReturn(\Mockery::mock(
                        'PhpAmqpLib\\Channel\\AMQPChannel',
                        function ($mock) {
                            $mock->shouldReceive('queue_declare')
                                ->andReturnUsing(function ($queue) {
                                    return $queue;
                                });

                           $mock->shouldReceive('exchange_declare')
                                ->andReturnUsing(function ($exchange) {
                                    return $exchange;
                                });

                            $mock->shouldReceive('basic_ack')
                                ->shouldReceive('basic_publish')
                                ->shouldReceive('close')
                                ->andReturnNull();
                        }
                    ));

                $mock->shouldReceive('push')
                    ->andReturnNull();

                $mock->shouldReceive('isConnected')
                    ->andReturn(false);
            }
        );

        $this->job = new RabbitJob($msg, $this->connector);
    }

    public function testJobProvider()
    {
        return array(
            array(array(
                'job' => 'Job@runThis',
                'data' => array(),
            ), true),
            array(array(
                'job' => 'Job@failThis',
                'data' => array(),
            ), null),
            array(array(
                'job' => 'Job@fake',
                'data' => array(),
            ), false),
            array(array(
                'job' => 'Fake',
                'data' => array(),
            ), false),
            array(array(
                'job' => 'Job@failThis:failedThis',
                'data' => array(),
            ), null),
            array(array(
                'job' => 'Indigo\\Queue\\Closure',
                'data' => array(),
                'closure' => serialize(new SerializableClosure(function () {
                    return true;
                })),
            ), true),
        );
    }

    /**
     * @dataProvider testJobProvider
     */
    public function testJob($payload, $return)
    {
        $msg = \Mockery::mock('PhpAmqpLib\\Message\\AMQPMessage');
        $msg->body = json_encode($payload);
        $msg->delivery_info = array('delivery_tag' => 1);

        $job = new RabbitJob($msg, $this->connector);

        $this->assertEquals($return, $job->execute());
    }

    public function testMessage()
    {
        $this->assertInstanceOf(
            'PhpAmqpLib\\Message\\AMQPMessage',
            $this->job->getMessage()
        );
    }
}