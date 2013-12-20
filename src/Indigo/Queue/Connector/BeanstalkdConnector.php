<?php
/*
 * This file is part of the Indigo Queue package.
 *
 * (c) IndigoPHP Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Connector;

use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Indigo\Queue\Job\BeanstalkdJob;

/**
 * Beanstalkd connector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class BeanstalkdConnector extends AbstractConnector
{
    /**
     * Pheanstalk object
     *
     * @var Pheanstalk
     */
    protected $pheanstalk = null;

    public function __construct($host, $port = Pheanstalk::DEFAULT_PORT, $timeout = null)
    {
        // Don't worry, Pheanstalk object injected
        if ($host instanceof Pheanstalk) {
            $this->pheanstalk = $host;
        } else {
            $this->pheanstalk = new Pheanstalk($host, $port, $timeout);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return $this->pheanstalk->getConnection()->isServiceListening();
    }

    /**
     * {@inheritdoc}
     */
    public function push(array $payload, array $options = array())
    {
        // Set default options
        $default = array(
            'delay'    => Pheanstalk::DEFAULT_DELAY,
            'ttr'      => Pheanstalk::DEFAULT_TTR,
            'priority' => Pheanstalk::DEFAULT_PRIORITY
        );

        $options = array_merge($default, $options);

        return $this->pheanstalk->putInTube($payload['queue'], json_encode($payload), $options['priority'], $options['delay'], $options['ttr']);
    }

    /**
     * {@inheritdoc}
     */
    public function delayed($delay, array $payload, array $options = array())
    {
        $options['delay'] = $delay;

        return $this->push($payload, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        $job = $this->pheanstalk->reserveFromTube($queue, $timeout);

        if ($job instanceof Pheanstalk_Job) {
            return new BeanstalkdJob($job, $this);
        }
    }

    /**
     * Return Pheanstalk object
     *
     * @return Pheanstalk
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }
}
