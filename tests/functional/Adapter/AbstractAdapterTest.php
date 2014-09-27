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

use Indigo\Queue\Job;
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
     * Provides jobs
     *
     * @return Job[]
     */
    public function jobProvider()
    {
        return [
            [new Job('DummyJob')],
            [new Job(function () {
                return true;
            })],
        ];
    }
}
