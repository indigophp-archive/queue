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

use Codeception\TestCase\Test;

/**
 * Tests for Adapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractAdapterTest extends Test
{
    /**
     * Adapter object
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * Returns Message mock
     *
     * @return Message
     */
    public function getMessageMock()
    {
        $mock = \Mockery::mock('Indigo\\Queue\\Message');

        $mock->shouldReceive('getQueue')
            ->andReturn('test');

        $mock->shouldReceive('getData')
            ->andReturn([]);

        $mock->shouldReceive('getId')
            ->andReturn(1);

        return $mock;
    }

    /**
     * @covers ::getMessageClass
     */
    public function testMessage()
    {
        $this->assertEquals('Indigo\\Queue\\Message\\Message', $this->adapter->getMessageClass());
    }

    /**
     * @covers ::isConnected
     */
    public function testConnection()
    {
        $this->assertTrue($this->adapter->isConnected());
    }

    /**
     * @covers ::push
     */
    public function testPush()
    {
        $message = $this->getMessageMock();

        $this->assertTrue($this->adapter->push($message));
    }

    /**
     * @covers ::pop
     */
    public function testPop()
    {
        $message = $this->adapter->pop('test');

        $this->assertEquals('test', $message->getQueue());
        $this->assertEquals(1, $message->getId());
        $this->assertEquals([], $message->getData());
        $this->assertEquals(1, $message->getAttempts());
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty
     */
    public function testEmptyPop()
    {
        $this->adapter->pop('test');
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $this->assertEquals(1, $this->adapter->count('test'));
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $message = $this->getMessageMock();

        $this->assertTrue($this->adapter->delete($message));
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $this->assertTrue($this->adapter->clear('test'));
    }

    /**
     * @covers ::release
     */
    public function testRelease()
    {
        $message = $this->getMessageMock();

        $this->assertTrue($this->adapter->release($message));
    }
}
