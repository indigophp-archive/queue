<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Adapter;

use Indigo\Queue\Adapter;

/**
 * Abstract Adapter class
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractAdapter implements Adapter
{
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

    public function __construct()
    {
        if (empty($this->managerClass)) {
            $this->managerClass = str_replace('Adapter', 'Manager', get_called_class());
        }
    }

    /**
     * Returns the manager class for Adapter
     *
     * @return string
     */
    public function getManagerClass()
    {
        return $this->managerClass;
    }
}
