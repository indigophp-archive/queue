<?php

namespace Test\Unit;

use Indigo\Queue\Manager\BeanstalkdManager;

/**
 * Tests for BeanstalkdManager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Manager\BeanstalkdManager
 * @group              Queue
 * @group              Manager
 * @group              Beanstalkd
 */
class BeanstalkdManagerTest extends AbstractManagerTest
{
    /**
     * Pheanstalk Job
     *
     * @var Pheanstalk\Job
     */
    protected $pheanstalkJob;

    public function _before()
    {
        $this->pheanstalkJob = \Mockery::mock('Pheanstalk\\Job');

        $this->pheanstalkJob->shouldReceive('getData')
            ->andReturn(json_encode([]));

        $this->connector = \Mockery::mock('Indigo\Queue\Connector\BeanstalkdConnector');

        $this->manager = new BeanstalkdManager('test', $this->pheanstalkJob, $this->connector);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $manager = new BeanstalkdManager('test', $this->pheanstalkJob, $this->connector);

        $this->assertSame($this->pheanstalkJob, $this->manager->getPheanstalkJob());
    }

    /**
     * @covers ::attempts
     */
    public function testAttempts()
    {
        $stat = new \stdClass;
        $stat->reserves = 1;

        $this->connector->shouldReceive('getPheanstalk->statsJob')
            ->andReturn($stat);

        $this->assertEquals(1, $this->manager->attempts());
    }

    /**
     * @covers ::getPheanstalkJob
     */
    public function testPheanstalkJob()
    {
        $this->assertSame($this->pheanstalkJob, $this->manager->getPheanstalkJob());
    }
}
