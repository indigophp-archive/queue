<?php

/*
 * This file is part of the Indigo Queue package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Queue;

/**
 * Defines a generic interface for Messages
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface Message
{
    /**
     * Returns the Message ID
     *
     * In case of responses it should return the job ID
     * In case of "requests" it can be ommited (depending on backend?)
     *
     * Do not mix the words request/response with HTTP Messages
     * They are just used here for simplicity
     *
     * @return mixed
     */
    public function getId();

    /**
     * Returns the queue
     *
     * @return string
     */
    public function getQueue();

    /**
     * Sets the queue
     *
     * @param string $queue
     *
     * @return self
     */
    public function setQueue($queue);

    /**
     * Returns the message data
     *
     * @return []
     */
    public function getData();

    /**
     * Sets the message data
     *
     * @param [] $data
     *
     * @return self
     */
    public function setData(array $data);

    /**
    * Returns the number of times the message has been reserved
    *
    * By definition zero should be returned
    * if the message has not been pushed to the queue yet
    *
    * @return integer
    */
    public function getAttempts();
}
