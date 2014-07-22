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
 * Job class
 *
 * You can push this class to queues
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Job
{
    /**
     * Job class name
     *
     * @var string
     */
    protected $job;

    /**
     * Data
     *
     * @var []
     */
    protected $data = [];

    /**
     * Options
     *
     * @var []
     */
    protected $options = [];

    /**
     * Closure class name
     *
     * @var string
     */
    protected $closureClass = 'Indigo\\Queue\\Job\\ClosureJob';

    /**
     * Creates a new Job
     *
     * @param string $job
     * @param []     $data
     * @param []     $options
     */
    public function __construct($job, array $data = [], array $options = [])
    {
        $this->job = $job;
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Returns the job
     *
     * @return string
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Sets the job
     *
     * @param string $job
     *
     * @return this
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Returns the data
     *
     * @return []
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data
     *
     * @param [] $data
     *
     * @return this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns the options
     *
     * @return []
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the options
     *
     * @param [] $options
     *
     * @return this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
    * Creates a serialized payload
    *
    * @return []
    */
    public function createPayload()
    {
        $payload = [
            'job'   => $this->job,
            'data'  => $this->data,
        ];

        // Create special payload if it is a Closure
        if ($this->job instanceof \Closure) {
            $payload['closure'] = serialize(new SerializableClosure($this->job));
            $payload['job'] = $this->closureClass;
        }

        return $payload;
    }
}
