<?php

namespace Indigo\Queue\Job;

use Jeremeamia\SuperClosure\SerializableClosure;

class BeanstalkdJobTest extends JobTest
{
    public function setUp()
    {
        $job = \Mockery::mock('Pheanstalk_Job');
        $job->shouldReceive('getData')
            ->andReturn(
                json_encode(array(
                    'job' => 'Job',
                    'data' => array(
                        'test',
                        'test2'
                    )
                ))
            );

        $this->connector = \Mockery::mock(
            'Indigo\\Queue\\Connector\\BeanstalkdConnector',
            function ($mock) {
                $mock->shouldReceive('getPheanstalk')
                    ->andReturn(\Mockery::mock(
                        'Pheanstalk_PheanstalkInterface',
                        function ($mock) {
                            $stats = new \stdClass;
                            $stats->reserves = 2;

                            $mock->shouldReceive('delete')
                                ->andReturn(true);

                            $mock->shouldReceive('bury')
                                ->andReturn(true);

                            $mock->shouldReceive('statsJob')
                                ->andReturn($stats);

                            $mock->shouldReceive('release')
                                ->andReturn(1);
                        }
                    ));
            }
        );

        $this->job = new BeanstalkdJob($job, $this->connector);
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
        $job = \Mockery::mock('Pheanstalk_Job');
        $job->shouldReceive('getData')
            ->andReturn(json_encode($payload));

        $job = new BeanstalkdJob($job, $this->connector);

        $this->assertEquals($return, $job->execute());
    }

    public function testPheanstalkJob()
    {
        $this->assertInstanceOf(
            'Pheanstalk_Job',
            $this->job->getPheanstalkJob()
        );
    }
}