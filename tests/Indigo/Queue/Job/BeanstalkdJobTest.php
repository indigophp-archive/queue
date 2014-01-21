<?php

namespace Indigo\Queue\Job;

/**
 * Beanstalkd Job Test
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
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
                        }
                    ));
            }
        );

        $this->job = new BeanstalkdJob($job, $this->connector);
    }

    public function testJob()
    {
        $job = \Mockery::mock('Pheanstalk_Job');
        $job->shouldReceive('getData')
            ->andReturn(
                json_encode(array(
                    'job' => 'Job@runThis',
                    'data' => array(),
                )),
                json_encode(array(
                    'job' => 'Job@failThis',
                    'data' => array(),
                )),
                json_encode(array(
                    'job' => 'Job@fake',
                    'data' => array(),
                )),
                json_encode(array(
                    'job' => 'Fake',
                    'data' => array(),
                )),
                json_encode(array(
                    'job' => 'Job@failThis:failedThis',
                    'data' => array(),
                ))
            );

        $job = new BeanstalkdJob($job, $this->connector);

        $this->assertTrue($job->execute());

        $job->getPayload(true);
        $this->assertNull($job->execute());

        $job->getPayload(true);
        $this->assertFalse($job->execute());

        $job->getPayload(true);
        $this->assertFalse($job->execute());

        $job->getPayload(true);
        $this->assertNull($job->execute());
    }
}