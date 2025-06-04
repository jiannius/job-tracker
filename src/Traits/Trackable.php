<?php

namespace Jiannius\JobTracker\Traits;

trait Trackable
{
    public $jobTracker;

    public function jobTracker()
    {
        if (!$this->jobTracker) {
            $this->jobTracker = \App\Models\JobTracker::where('uuid', $this->job->uuid())->first();
        }

        return $this->jobTracker;
    }

    public static function deleteJobTrackerOnFinished($jobTracker) : bool
    {
        return empty($jobTracker->errors);
    }
}