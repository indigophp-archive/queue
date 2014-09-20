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

use Indigo\Queue\Connector\IronConnector;
use Indigo\Queue\Job;
use IronMQ;

/**
 * Tests for IronConnector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Queue\Connector\IronConnector
 * @group              Queue
 * @group              Connector
 * @group              Iron
 */
class IronConnectorTest extends AbstractMQConnectorTest
{
    public function _before()
    {
        if (getenv('IRON_TOKEN') and getenv('IRON_PROJECT_ID')) {
            $config = array(
                'token'      => getenv('IRON_TOKEN'),
                'project_id' => getenv('IRON_PROJECT_ID'),
            );
        } elseif (isset($GLOBALS['iron_token']) and isset($GLOBALS['iron_project_id'])) {
            $config = array(
                'token'      => $GLOBALS['iron_token'],
                'project_id' => $GLOBALS['iron_project_id'],
            );
        } else {
            $this->markTestSkipped(
                'IronMQ credentials are not available.'
            );
        }

        $config['protocol'] = 'http';
        $config['port'] = 80;

        $iron = new IronMQ($config);

        $iron->ssl_verifypeer = false;

        $this->connector = new IronConnector($iron);

        $this->connector->clear('test');
        $this->connector->clear('test_clear');
        $this->connector->clear('test_count');
    }

    /**
     * @covers       ::push
     * @dataProvider jobProvider
     */
    public function testPush(Job $job)
    {
        $push = $this->connector->push('test', $job);

        $this->assertInstanceOf('stdClass', $push);
    }

    /**
     * @covers       ::delayed
     * @dataProvider jobProvider
     */
    public function testDelayed(Job $job)
    {
        $delayed = $this->connector->delayed('test', 1, $job);

        $this->assertInstanceOf('stdClass', $delayed);
    }
}
