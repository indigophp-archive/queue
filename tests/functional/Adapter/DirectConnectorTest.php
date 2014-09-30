<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Functional;

use Indigo\Queue\Adapter\DirectAdapter;
use Indigo\Queue\Job;

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
     * @covers       ::push
     * @covers       ::pop
     * @dataProvider jobProvider
     */
    public function testPush(Job $job)
    {
        $this->assertTrue($this->adapter->push('test', $job));
    }
}
