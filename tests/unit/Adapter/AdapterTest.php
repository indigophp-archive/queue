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

/**
 * Tests for AbstractAdapter
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Adapter\AbstractAdapter
 * @group              Queue
 * @group              Adapter
 */
class AdapterTest extends AbstractAdapterTest
{
    public function _before()
    {
        $this->adapter = new \DummyAdapter;
    }

    /**
     * @covers ::getManagerClass
     */
    public function testManager()
    {
        $this->assertEquals('Fake\\Class', $this->adapter->getManagerClass());
    }
}
