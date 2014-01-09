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

use Indigo\Queue\Job\BeanstalkdJob;
use Pheanstalk_Job;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Pheanstalk_PheanstalkInterface as PheanstalkInterface;

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

    public function __construct($host, $port = PheanstalkInterface::DEFAULT_PORT, $timeout = null)
    {
        // Don't worry, Pheanstalk object injected
        if ($host instanceof PheanstalkInterface) {
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
            'delay'    => PheanstalkInterface::DEFAULT_DELAY,
            'ttr'      => PheanstalkInterface::DEFAULT_TTR,
            'priority' => PheanstalkInterface::DEFAULT_PRIORITY
        );

        $options = array_merge($default, $options);

        return $this->pheanstalk->putInTube(
            $payload['queue'],
            json_encode($payload),
            $options['priority'],
            $options['delay'],
            $options['ttr']
        );
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

    /**
     * Set Pheanstalk object
     *
     * @param  PheanstalkInterface $pheanstalk
     * @return BeanstalkdConnector
     */
    public function setPheanstalk(PheanstalkInterface $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;

        return $this;
    }
}
