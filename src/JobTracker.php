<?php

namespace Jiannius\JobTracker;

use Illuminate\Contracts\Queue\Job as JobContract;
use Jiannius\JobTracker\Enums\JobTrackerStatus;
use Jiannius\JobTracker\Traits\Trackable;

class JobTracker
{
    /**
     * Handle the queued event.
     */
    public function queued($event) : void
    {
        $tracker = $this->getTracker($event);

        if (!$tracker) return;

        $tracker->reset();
    }

    /**
     * Handle the running event.
     */
    public function running($event) : void
    {
        $tracker = $this->getTracker($event);

        if (!$tracker) return;

        $tracker->start();
        $tracker->update(['attempts' => $event->job->attempts()]);
    }

    /**
     * Handle the finished event.
     */
    public function finished($event) : void
    {
        $tracker = $this->getTracker($event);

        if (!$tracker) return;

        $jobClass = $event->job->resolveName();

        if ($jobClass::deleteJobTrackerOnFinished($tracker)) {
            $tracker->delete();
            return;
        }

        $tracker->finished();
    }

    /**
     * Handle the failed event.
     */
    public function failed($event)
    {
        $tracker = $this->getTracker($event);

        if (!$tracker) return;

        $tracker->setErrors($event->exception->getMessage());
        $tracker->failed();
    }

    /**
     * Handle the exception event.
     */
    public function exception($event)
    {
        if ($event->job->hasFailed()) return;

        $tracker = $this->getTracker($event);

        if (!$tracker) return;

        $tracker->setErrors($event->exception->getMessage());
    }

    /**
     * Get the job tracker.
     */
    public function getTracker($event)
    {
        // Get the payload from the event
        // if using laravel horizon, we get the payload from $event->payload->decoded
        $payload = $event->job instanceof JobContract && method_exists($event->job, 'payload')
            ? $event->job->payload()
            : (isset($event->payload->decoded) ? $event->payload->decoded : $event->payload());

        $uuid = data_get($payload, 'uuid');
        $name = data_get($payload, 'displayName');
        
        if (!$uuid) return;
        
        $trackable = in_array(Trackable::class, class_uses_recursive($name));

        if (!$trackable) return;

        $tracker = \App\Models\JobTracker::query()
            ->withoutGlobalScopes()
            ->where('uuid', $uuid)
            ->whereIn('status', [JobTrackerStatus::QUEUED, JobTrackerStatus::RUNNING])
            ->first();

        if (!$tracker) {
            $tracker = \App\Models\JobTracker::create([
                'uuid' => $uuid,
                'name' => $name,
            ]);
        }

        return $tracker;
    }
}
