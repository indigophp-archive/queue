<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue\Message;

use Indigo\Queue\Message as MessageInterface;

/**
 * Implements simple message logic
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Message implements MessageInterface
{
    /**
     * Queue name
     *
     * @var string
     */
    private $queue;

    /**
     * Message data
     *
     * @var []
     */
    private $data;

    /**
     * Message ID
     *
     * @var mixed
     */
    private $id;

    /**
     * Number of attempts
     *
     * @var integer
     */
    private $attempts;

    /**
     * @param string  $queue
     * @param []      $data
     * @param mixed   $id
     * @param integer $attempts
     */
    public function __construct($queue, array $data, $id = null, $attempts = 0)
    {
        $this->queue = $queue;
        $this->data = $data;
        $this->id = $id;
        $this->attempts = $attempts;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttempts()
    {
        return $this->attempts;
    }
}
