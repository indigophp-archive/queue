<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Unit\Job;

use Codeception\TestCase\Test;

/**
 * Tests for Jobs
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractJobTest extends Test
{
    /**
     * Returns a new ManagerInterface
     *
     * @return ManagerInterface
     */
    public function getManagerMock()
    {
        $mock = \Mockery::mock('Indigo\\Queue\\Manager\\ManagerInterface');

        $mock->shouldReceive('getPayload')
            ->andReturn([])
            ->byDefault();

        return $mock;
    }
}
