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

use Indigo\Queue\Job\JobInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract Connector class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractConnector implements ConnectorInterface, LoggerAwareInterface
{
    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Default job options
     *
     * @var array
     */
    protected $jobOptions = array(
        'delay'   => 0,
        'timeout' => 60,
    );

    /**
     * Resolve job options
     *
     * @param  array  $options
     * @return array Resolved options
     */
    protected function resolveJobOptions(array $options)
    {
        static $resolver;

        if (!$resolver instanceof OptionsResolver) {
            $resolver = new OptionsResolver;
            $this->setDefaultJobOptions($resolver);
        }

        return $resolver->resolve($options);
    }

    /**
     * Set default job options
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultJobOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults($this->jobOptions)
            ->setAllowedTypes(array_fill_keys(array_keys($this->jobOptions), 'integer'));
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
