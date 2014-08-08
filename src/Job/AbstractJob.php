<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Job;

use Indigo\Queue\Manager\ManagerInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Abstract Job
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
abstract class AbstractJob implements JobInterface, LoggerAwareInterface
{
	use \Psr\Log\LoggerAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function fail(ManagerInterface $manager, \Exception $e)
	{
		$this->logger->error($e->getMessage());

		return true;
	}
}
