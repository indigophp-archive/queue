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

use Indigo\Queue\Job\IronJob;
use IronMQ;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Psr\Log\NullLogger;

/**
 * Iron connector
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class IronConnector extends AbstractConnector
{
    /**
     * IronMQ object
     *
     * @var IronMQ
     */
    protected $iron = null;

    /**
     * Job options
     *
     * @var array
     */
    protected $jobOptions = array(
        'delay'   => 0,
        'timeout' => 0,
    );

    public function __construct(IronMQ $iron)
    {
        $this->iron = $iron;

        $this->setLogger(new NullLogger);
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function push(array $payload, array $options = array())
    {
        $options = $this->resolveMessageOptions($options);

        return $this->iron->postMessage(
            $payload['queue'],
            json_encode($payload),
            $options
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

    protected function resolveMessageOptions(array $options)
    {
        static $resolver;

        if (!$resolver instanceof OptionsResolver) {
            $resolver = new OptionsResolver;
            $resolver->setDefaults($this->jobOptions)
                ->setAllowedTypes(array_fill_keys(array_keys($this->jobOptions), 'integer'));
        }

        return $resolver->resolve($options);
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue, $timeout = 0)
    {
        if ($job = $this->iron->getMessage($queue, $timeout)) {
            return new IronJob($job, $this);
        }
    }

    /**
     * Return IronMQ object
     *
     * @return IronMQ
     */
    public function getIron()
    {
        return $this->iron;
    }

    /**
     * Set Pheanstalk object
     *
     * @param  IronMQ        $iron
     * @return IronConnector
     */
    public function setIron(IronMQ $iron)
    {
        $this->iron = $iron;

        return $this;
    }
}
