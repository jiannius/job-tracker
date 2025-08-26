<?php

namespace Jiannius\JobTracker\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * WithJobTracker trait - to be consumed by the livewire component
 */
trait WithJobTracker
{
    public $jobTracker;

    /**
     * Get the job tracker
     */
    public function getJobTracker($name) : void
    {
        $this->jobTracker = \App\Models\JobTracker::query()
            ->where('name', $name)
            ->when(Auth::check(), fn ($query) => $query->where('user_id', Auth::id()))
            ->latest()
            ->first();
    }

    /**
     * Get the job tracker progress
     */
    public function getJobTrackerProgress() : array
    {
        $this->skipRender();

        $this->jobTracker->refresh();

        return $this->jobTracker->toArray();
    }

    /**
     * Delete the job tracker
     */
    public function deleteJobTracker() : void
    {
        $this->jobTracker?->delete();
        $this->reset('jobTracker');
    }
}