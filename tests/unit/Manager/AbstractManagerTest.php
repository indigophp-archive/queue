<?php

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
