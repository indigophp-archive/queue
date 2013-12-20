<?php
/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Connector;

use Indigo\Queue\Queue;
use Indigo\Queue\Worker;

/**
 * BeanstalkdConnector Test
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class BeanstalkdConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Connector
     *
     * @var ConnectorInterface
     */
    protected $connector = null;

    /**
     * Queue
     *
     * @var Queue
     */
    protected $queue;

    /**
     * Worker
     *
     * @var Worker
     */
    protected $worker = null;

    public function setUp()
    {
        $this->connector = new BeanstalkdConnector('localhost');

        $this->worker = new Worker('test', $this->connector);

        $this->queue = new Queue('test', $this->connector);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testConnection()
    {
        $this->assertTrue($this->connector->isConnected(), 'Connection is not present to beanstalkd');
    }

    public function testPheanstalk()
    {
        $this->assertInstanceOf('Pheanstalk_Pheanstalk', $this->connector->getPheanstalk(), 'No Pheanstalk object available');
    }

    public function testClosure()
    {
        $this->queue->push(function() { return true; });

        $this->assertTrue($this->worker->work());
    }

    public function testClosureDelayed()
    {
        $this->queue->delayed(1, function() { return true; });

        $this->assertTrue($this->worker->work(2));
    }

    public function testJob()
    {
        $this->queue->push('Job');

        $this->assertTrue($this->worker->work());
    }

    public function testJobExecute()
    {
        $this->queue->push('Job@runThis');

        $this->assertTrue($this->worker->work());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testJobExecuteFailure()
    {
        $this->queue->push('Job@failThis:failedThis');

        $this->worker->work();
    }
}