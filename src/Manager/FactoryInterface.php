<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Manager;

use Indigo\Queue\Manager;

/**
 * Manager Factory
 *
 * Queue instantiable class by adding a factory method
 *
 * Can be used with JobInterface objects and other instantiables
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface FactoryInterface
{
    /**
     * Factory method
     *
     * @param Manager $manager
     *
     * @return mixed
     */
    public static function factory(Manager $manager);
}
