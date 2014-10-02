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
        $worker = \Mockery::mock('Indigo\\Queue\\Worker');

        $worker->shouldReceive('work')
            ->andReturn(true);

        $this->adapter = new DirectAdapter($worker);
    }

    /**
     * @covers ::push
     * @covers ::pop
     */
    public function testPush()
    {
        parent::testPush();
    }

    /**
     * @covers ::pop
     */
    public function testPop()
    {
        // This test is is run with testPush
    }

    public function testMessage()
    {
        // This adapter does not need a messageClass
    }
}
