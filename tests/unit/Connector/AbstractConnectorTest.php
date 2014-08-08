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
 * Tests for ConnectorInterface
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractConnectorTest extends Test
{
    /**
     * Connector object
     *
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * Returns manager mock
     *
     * @return ManagerInterface
     */
    public function getManagerMock()
    {
        return \Mockery::mock('Indigo\\Queue\\Manager\\ManagerInterface');
    }
}
