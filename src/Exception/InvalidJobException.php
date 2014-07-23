<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Exception;

/**
 * Invalid Job Exception
 *
 * Connectors should throw this exception if no job can be popped
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class InvalidJobException extends \InvalidArgumentException
{
}
