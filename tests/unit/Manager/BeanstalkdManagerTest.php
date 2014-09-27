<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

        $this->adapter = \Mockery::mock('Indigo\\Queue\\Adapter\\BeanstalkdAdapter');

        $this->manager = new BeanstalkdManager('test', $this->pheanstalkJob, $this->adapter);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $manager = new BeanstalkdManager('test', $this->pheanstalkJob, $this->adapter);

        $this->assertSame($this->pheanstalkJob, $this->manager->getPheanstalkJob());
    }

    /**
     * @covers ::attempts
     */
    public function testAttempts()
    {
        $stat = new \stdClass;
        $stat->reserves = 1;

        $this->adapter->shouldReceive('getPheanstalk->statsJob')
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
