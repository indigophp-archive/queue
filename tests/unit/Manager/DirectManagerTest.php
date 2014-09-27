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

use Indigo\Queue\Manager\DirectManager;

/**
 * Tests for DirectManager
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Manager\DirectManager
 * @group              Queue
 * @group              Manager
 * @group              Direct
 */
class DirectManagerTest extends AbstractManagerTest
{
    public function _before()
    {
        $this->adapter = \Mockery::mock('Indigo\\Queue\\Adapter\\DirectAdapter');

        $this->manager = new DirectManager('test', ['payload'], $this->adapter);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $manager = new DirectManager('test', ['payload'], $this->adapter);

        $this->assertEquals('test', $manager->getQueue());
        $this->assertEquals(['payload'], $manager->getPayload());
        $this->assertSame($this->adapter, $manager->getAdapter());
    }

    /**
     * @covers ::attempts
     */
    public function testAttempts()
    {
        $this->assertEquals(1, $this->manager->attempts());
    }
}