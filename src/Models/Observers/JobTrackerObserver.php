<?php

namespace Jiannius\JobTracker\Models\Observers;

use Illuminate\Support\Facades\Storage;
use Jiannius\JobTracker\Enums\JobTrackerStatus;

class JobTrackerObserver
{
    /**
     * Handle the saving event.
     */
    public function saving($tracker): void
    {
        $tracker->fill([
            'expires_at' => $tracker->expires_at ?? now()->addDays(7),
            'created_by' => $tracker->created_by ?? auth()->id(),
            'status' => match (true) {
                !empty($tracker->finished_at) => JobTrackerStatus::FINISHED,
                !empty($tracker->stopped_at) => JobTrackerStatus::STOPPED,
                !empty($tracker->failed_at) => JobTrackerStatus::FAILED,
                !empty($tracker->started_at) => JobTrackerStatus::RUNNING,
                $tracker->expires_at?->isPast() => JobTrackerStatus::EXPIRED,
                default => JobTrackerStatus::QUEUED,
            },
            'filename' => $tracker->filename ?? (
                $tracker->path
                ? str($tracker->path)->afterLast('/')->toString()
                : null
            ),
        ]);
    }

    /**
     * Handle the deleting event.
     */
    public function deleting($tracker): void
    {
        if (!$tracker->path) return;
        if (!file_exists(storage_path('app/private/'.$tracker->path))) return;

        Storage::disk('local')->delete($tracker->path);
    }
}
