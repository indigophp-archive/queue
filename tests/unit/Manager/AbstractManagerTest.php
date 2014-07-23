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

use Jeremeamia\SuperClosure\SerializableClosure;
use Codeception\TestCase\Test;

/**
 * Tests for ManagerInterface
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractManagerTest extends Test
{
    /**
     * Connector mock
     *
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * Manager object
     *
     * @var ManagerInterface
     */
    protected $manager;
}
