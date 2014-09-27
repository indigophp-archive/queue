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

use Indigo\Queue\Adapter\BeanstalkdAdapter;

/**
 * Tests for BeanstalkdAdapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Adapter\BeanstalkdAdapter
 * @group              Queue
 * @group              Adapter
 * @group              Beanstalkd
 */
class BeanstalkdAdapterTest extends AbstractAdapterTest
{
    public function _before()
    {
        $pheanstalk = \Mockery::mock('Pheanstalk\\Pheanstalk');

        $this->adapter = new BeanstalkdAdapter($pheanstalk);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $pheanstalk = \Mockery::mock('Pheanstalk\\Pheanstalk');

        $adapter = new BeanstalkdAdapter($pheanstalk);

        $this->assertSame($pheanstalk, $adapter->getPheanstalk());
    }

    /**
     * @covers ::isConnected
     */
    public function testConnection()
    {
        $pheanstalk = $this->adapter->getPheanstalk();

        $pheanstalk->shouldReceive('getConnection->isServiceListening')
            ->andReturn(true);

        $this->assertTrue($this->adapter->isConnected());
    }

    /**
     * @covers ::getPheanstalk
     * @covers ::setPheanstalk
     */
    public function testPheanstalk()
    {
        $pheanstalk = \Mockery::mock('Pheanstalk\\Pheanstalk');

        $this->assertSame($this->adapter, $this->adapter->setPheanstalk($pheanstalk));

        $this->assertSame($pheanstalk, $this->adapter->getPheanstalk());
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty.
     */
    public function testEmptyPop()
    {
        $pheanstalk = $this->adapter->getPheanstalk();

        $pheanstalk->shouldReceive('reserveFromTube')
            ->andReturn(null);

        $this->adapter->pop('test');
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $pheanstalk = $this->adapter->getPheanstalk();

        $pheanstalk->shouldReceive('statsTube')
            ->andReturn(['current-jobs-ready' => 1]);

        $this->assertEquals(1, $this->adapter->count('test'));
    }
}
