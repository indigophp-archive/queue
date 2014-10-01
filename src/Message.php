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
     * Returns the options
     *
     * @return []
     */
    public function getOptions();

    /**
     * Sets the options
     *
     * @param [] $options
     *
     * @return self
     */
    public function setOptions(array $options);
}
