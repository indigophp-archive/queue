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
    /**
     * Pheanstalk mock
     *
     * @var Pheanstalk
     */
    protected $pheanstalk;

    public function _before()
    {
        $this->pheanstalk = \Mockery::mock('Pheanstalk\\Pheanstalk');

        $this->adapter = new BeanstalkdAdapter($this->pheanstalk);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $adapter = new BeanstalkdAdapter($this->pheanstalk);

        $this->assertSame($this->pheanstalk, $adapter->getPheanstalk());
    }

    /**
     * @covers ::isConnected
     */
    public function testConnection()
    {
        $this->pheanstalk->shouldReceive('getConnection->isServiceListening')
            ->andReturn(true);

        parent::testConnection();
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
     * @covers ::push
     * @covers ::getDelay
     * @covers ::getPriority
     */
    public function testPush()
    {
        $this->pheanstalk->shouldReceive('putInTube')
            ->andReturn(true);

        parent::testPush();
    }

    /**
     * @covers ::pop
     */
    public function testPop()
    {
        $message = \Mockery::mock('Pheanstalk\\Job');

        $message->shouldReceive('getId')
            ->andReturn(1);
        $message->shouldReceive('getData')
            ->andReturn(json_encode([]));

        $this->pheanstalk->shouldReceive('reserveFromTube')
            ->andReturn($message);

        $stats = new \stdClass;
        $stats->reserves = 1;

        $this->pheanstalk->shouldReceive('statsJob')
            ->andReturn($stats);

        parent::testPop();
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty
     */
    public function testEmptyPop()
    {
        $this->pheanstalk->shouldReceive('reserveFromTube')
            ->andReturn(null);

        parent::testEmptyPop();
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $this->pheanstalk->shouldReceive('statsTube')
            ->andReturn(['current-jobs-ready' => 1]);

        parent::testCount();
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $this->pheanstalk->shouldReceive('delete');

        parent::testDelete();
    }

    /**
     * @covers ::bury
     */
    public function testBury()
    {
        $this->adapter->getPheanstalk()
            ->shouldReceive('bury');

        $message = $this->getMessageMock();

        $this->assertTrue($this->adapter->bury($message));
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $this->adapter->getPheanstalk()
            ->shouldReceive('delete')
            ->shouldReceive('peekReady')
            ->shouldReceive('peekBuried')
            ->shouldReceive('peekDelayed');

        parent::testClear();
    }

    /**
     * @covers ::release
     * @covers ::getPriority
     * @covers ::getDelay
     */
    public function testRelease()
    {
        $this->adapter->getPheanstalk()
            ->shouldReceive('release');

        parent::testRelease();
    }
}
