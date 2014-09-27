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

use Indigo\Queue\Adapter\DirectAdapter;

/**
 * Tests for DirectAdapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Adapter\DirectAdapter
 * @group              Queue
 * @group              Adapter
 * @group              Direct
 */
class DirectAdapterTest extends AbstractAdapterTest
{
    public function _before()
    {
        $this->adapter = new DirectAdapter;
    }

    /**
     * @covers ::isConnected
     */
    public function testConnection()
    {
        $this->assertTrue($this->adapter->isConnected());
    }

    /**
     * @covers                   ::pop
     * @covers                   Indigo\Queue\Exception\QueueEmptyException
     * @expectedException        Indigo\Queue\Exception\QueueEmptyException
     * @expectedExceptionMessage Queue test is empty.
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
        $this->assertEquals(1, $this->adapter->count(''));
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $this->assertTrue($this->adapter->delete($this->getManagerMock()));
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $this->assertTrue($this->adapter->clear(''));
    }

    /**
     * @covers ::release
     */
    public function testRelease()
    {
        $this->assertTrue($this->adapter->release($this->getManagerMock()));
    }
}
