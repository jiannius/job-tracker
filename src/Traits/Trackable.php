<?php

namespace Jiannius\JobTracker\Traits;

/**
 * Trackable trait - to be consumed by the job
 */
trait Trackable
{
    public $tracker;

    /**
     * Get the job tracker.
     */
    public function tracker()
    {
        $this->tracker ??= \App\Models\JobTracker::withoutGlobalScopes()
            ->where('uuid', $this->job->uuid())
            ->first();

        return $this->tracker;
    }

    /**
     * Delete the job tracker on finished.
     */
    public static function deleteJobTrackerOnFinished($tracker) : bool
    {
        return false;
    }
}