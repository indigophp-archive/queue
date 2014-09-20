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

use Indigo\Queue\Connector;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;

/**
 * Abstract Connector class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractConnector implements Connector, LoggerAwareInterface
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
     * Manager class to be instantiated
     *
     * @var string
     */
    protected $managerClass;

    /**
     * Creates a new Connector
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->setLogger(new NullLogger);

        if (empty($this->managerClass)) {
            $this->managerClass = str_replace('Connector', 'Manager', get_called_class());
        }
    }

    /**
     * Returns the manager class for connector
     *
     * @return string
     */
    public function getManagerClass()
    {
        return $this->managerClass;
    }
}
