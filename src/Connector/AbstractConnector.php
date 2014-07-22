<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Connector;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Psr\Log\NullLogger;

/**
 * Abstract Connector class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractConnector implements ConnectorInterface
{
    use \Psr\Log\LoggerAwareTrait;

    /**
     * Default job options
     *
     * @var []
     */
    protected $options = [
        'delay'   => 0,
        'timeout' => 60,
    ];

    /**
     * Job class to be instantiated
     *
     * @var string
     */
    protected $jobClass;

    /**
     * Creates a new Connector
     */
    public function __construct()
    {
        $this->setLogger(new NullLogger);
    }
}
