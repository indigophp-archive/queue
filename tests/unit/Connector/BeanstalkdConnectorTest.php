<?php

namespace Test\Unit;

use Indigo\Queue\Connector\BeanstalkdConnector;

/**
 * Tests for BeanstalkdConnector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Connector\BeanstalkdConnector
 * @group              Queue
 * @group              Connector
 * @group              Beanstalkd
 */
class BeanstalkdConnectorTest extends AbstractConnectorTest
{
    public function _before()
    {
        $pheanstalk = \Mockery::mock('Pheanstalk\\Pheanstalk');

        $this->connector = new BeanstalkdConnector($pheanstalk);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $pheanstalk = \Mockery::mock('Pheanstalk\\Pheanstalk');

        $connector = new BeanstalkdConnector($pheanstalk);

        $this->assertSame($pheanstalk, $connector->getPheanstalk());
    }

    /**
     * @covers ::isConnected
     */
    public function testConnection()
    {
        $pheanstalk = $this->connector->getPheanstalk();

        $pheanstalk->shouldReceive('getConnection->isServiceListening')
            ->andReturn(true);

        $this->assertTrue($this->connector->isConnected());
    }

    /**
     * @covers ::getPheanstalk
     * @covers ::setPheanstalk
     */
    public function testPheanstalk()
    {
        $pheanstalk = \Mockery::mock('Pheanstalk\\Pheanstalk');

        $this->assertSame($this->connector, $this->connector->setPheanstalk($pheanstalk));

        $this->assertSame($pheanstalk, $this->connector->getPheanstalk());
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty.
     */
    public function testEmptyPop()
    {
        $pheanstalk = $this->connector->getPheanstalk();

        $pheanstalk->shouldReceive('reserveFromTube')
            ->andReturn(null);

        $this->connector->pop('test');
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $pheanstalk = $this->connector->getPheanstalk();

        $pheanstalk->shouldReceive('statsTube')
            ->andReturn(['current-jobs-ready' => 1]);

        $this->assertEquals(1, $this->connector->count('test'));
    }
}
