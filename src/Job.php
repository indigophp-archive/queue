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

use Jeremeamia\SuperClosure\SerializableClosure;

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
     * Extras
     *
     * @var []
     */
    protected $extras = [];

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
    public function __construct($job, array $data = [], array $options = [], array $extras = [])
    {
        $this->job = $job;
        $this->data = $data;
        $this->options = $options;
        $this->extras = $extras;
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
     * Returns the extras
     *
     * @return []
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * Sets the extras
     *
     * @param [] $extras
     *
     * @return this
     */
    public function setExtras(array $extras)
    {
        $this->extras = $extras;

        return $this;
    }

    /**
    * Creates a serialized payload
    *
    * @return []
    */
    public function createPayload()
    {
        $payload = $this->extras;

        $payload['job'] = $this->job;
        $payload['data'] = $this->data;

        // Create special payload if it is a Closure
        if ($this->job instanceof \Closure) {
            $payload['closure'] = serialize(new SerializableClosure($this->job));
            $payload['job'] = $this->closureClass;
        }

        return $payload;
    }

    /**
     * Creates a new Job from payload
     *
     * @param [] $payload
     *
     * @return self
     */
    public static function createFromPayload(array $payload)
    {
        $job = $payload['job'];
        $data = $payload['data'];

        unset($payload['job'], $payload['data']);

        return new static($job, $data, [], $payload);
    }
}
