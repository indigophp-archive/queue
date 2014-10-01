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

use Indigo\Queue\Message;

/**
 * Implements connection details
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface Adapter
{
    /**
     * Checks whether a connection is available
     *
     * @return boolean
     */
    public function isConnected();

    /**
    * Pushes a new message onto the queue
    *
    * @param string  $queue
    * @param Message $message
    *
    * @return mixed
    */
    public function push($queue, Message $message);

    /**
    * Pops the next message off the queue
    *
    * @param string  $queue   Name of the queue
    * @param integer $timeout Wait timeout
    *
    * @return Message
    *
    * @throws QueueEmptyException If the queue is empty or no message is received
    */
    public function pop($queue, $timeout = 0);

    /**
     * Returns the count of messages
     *
     * @param string $queue
     *
     * @return integer
     */
    public function count($queue);

    /**
     * Deletes a message from queue
     *
     * @param Message $message
     *
     * @return boolean Returns false on detectable failure, true otherwise
     */
    public function delete(Message $message);

    /**
     * Clears the queue
     *
     * @param string $queue
     *
     * @return boolean Returns false on detectable failure, true otherwise
     */
    public function clear($queue);

    /**
     * Releases a message back to the queue
     *
     * @param Message $message
     *
     * @return boolean Returns false on detectable failure, true otherwise
     */
    public function release(Message $message);
}
