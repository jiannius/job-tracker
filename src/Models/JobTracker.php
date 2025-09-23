<?php

namespace Jiannius\JobTracker\Models;

use Jiannius\JobTracker\Enums\JobTrackerStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jiannius\JobTracker\Models\Observers\JobTrackerObserver;

class JobTracker extends Model
{
    use HasFactory;
    use HasUlids;

    protected $guarded = [];

    protected $casts = [
        'progress' => 'integer',
        'attempts' => 'integer',
        'status' => JobTrackerStatus::class,
        'data' => 'array',
        'errors' => 'array',
        'messages' => 'array',
        'is_downloadable' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'failed_at' => 'datetime',
        'stopped_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * The "booted" method for the model
     */
    protected static function booted()
    {
        static::observe(new JobTrackerObserver);
    }

    /**
     * Get the user that owns the job tracker.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Reset the job tracker.
     */
    public function reset()
    {
        $this->update([
            'started_at' => null,
            'finished_at' => null,
            'failed_at' => null,
            'stopped_at' => null,
            'expires_at' => null,
            'progress' => null,
            'attempts' => 0,
            'messages' => [],
            'errors' => [],
            'data' => [],
        ]);
    }

    /**
     * Check if the job tracker is queued.
     */
    public function isQueued() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::QUEUED;
    }

    /**
     * Check if the job tracker is running.
     */
    public function isRunning() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::RUNNING;
    }

    /**
     * Check if the job tracker is finished.
     */
    public function isFinished() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::FINISHED;
    }

    /**
     * Check if the job tracker is failed.
     */
    public function isFailed() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::FAILED;
    }

    /**
     * Check if the job tracker is stopped.
     */
    public function isStopped() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::STOPPED;
    }

    /**
     * Check if the job tracker is expired.
     */
    public function isExpired() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::EXPIRED;
    }

    /**
     * Set the errors for the job tracker.
     */
    public function setErrors(string $error)
    {
        $errors = $this->errors ?? [];
        $errors[] = $error;
        $this->update(['errors' => $errors]);

        return $this;
    }

    /**
     * Set the messages for the job tracker.
     */
    public function setMessages(string $message)
    {
        $messages = $this->messages ?? [];
        $messages[] = $message;
        $this->update(['messages' => $messages]);

        return $this;
    }

    /**
     * Set the progress for the job tracker.
     */
    public function setProgress($progress = 0, $total = null)
    {
        if ($total) {
            $progress = $total <= 0 ? 0 : min(100, round(($progress / $total) * 100));
        }

        $this->update(['progress' => $progress]);

        return $this;
    }

    /**
     * Start the job tracker.
     */
    public function start() : void
    {
        $this->update(['started_at' => now()]);
    }

    /**
     * Stop the job tracker.
     */
    public function stop() : void
    {
        $this->update(['stopped_at' => now()]);
    }

    /**
     * Finish the job tracker.
     */
    public function finished() : void
    {
        $this->update(['finished_at' => now()]);
    }

    /**
     * Fail the job tracker.
     */
    public function failed() : void
    {
        $this->update(['failed_at' => now()]);
    }
}
