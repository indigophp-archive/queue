<?php

namespace Phresque\Job;

interface JobInterface
{
	/**
    * Execute the job.
    *
    * @return void
    */
    public function execute();

    /**
    * Delete the job from the queue.
    *
    * @return void
    */
    public function delete();

    /**
    * Release the job back into the queue.
    *
    * @param  int      $delay
    * @param  int|null $priority
    * @return void
    */
    public function release($delay = 0, $priority = null);

    /**
    * Get the number of times the job has been attempted.
    *
    * @return int
    */
    public function attempts();

    public function getPayload();
}
