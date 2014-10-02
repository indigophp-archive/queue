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
use Indigo\Queue\Message;
use Indigo\Queue\Message\Delayed;

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

    protected $messageClass = 'Indigo\\Queue\\Message\\Message';

    /**
     * Returns the message class
     *
     * @return string
     */
    public function getMessageClass()
    {
        return $this->messageClass;
    }

    /**
     * Returns the message delay
     *
     * @param Message $message
     *
     * @return integer
     */
    protected function getDelay(Message $message)
    {
        if ($message instanceof Delayed) {
            return $message->getDelay();
        }

        return $this->options['delay'];
    }
}
