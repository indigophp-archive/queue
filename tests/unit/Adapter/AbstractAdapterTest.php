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
     * Returns manager mock
     *
     * @return Manager
     */
    public function getManagerMock()
    {
        return \Mockery::mock('Indigo\\Queue\\Manager');
    }
}
