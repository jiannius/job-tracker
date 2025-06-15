<?php

namespace Jiannius\JobTracker\Models;

use Jiannius\JobTracker\Enums\JobTrackerStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jiannius\JobTracker\Observers\JobTrackerObserver;

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

    protected static function booted()
    {
        static::observe(new JobTrackerObserver);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

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

    public function isQueued() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::QUEUED;
    }

    public function isRunning() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::RUNNING;
    }

    public function isFinished() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::FINISHED;
    }

    public function isFailed() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::FAILED;
    }

    public function isStopped() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::STOPPED;
    }

    public function isExpired() : bool
    {
        return $this->fresh()->status === JobTrackerStatus::EXPIRED;
    }

    public function setErrors(string $error)
    {
        $errors = $this->errors ?? [];
        $errors[] = $error;
        $this->update(['errors' => $errors]);

        return $this;
    }

    public function setMessages(string $message)
    {
        $messages = $this->messages ?? [];
        $messages[] = $message;
        $this->update(['messages' => $messages]);

        return $this;
    }

    public function setProgress($progress, $total = null)
    {
        if ($total) {
            $progress = $total <= 0 ? 0 : min(100, round(($progress / $total) * 100));
        }

        $this->update(['progress' => $progress]);

        return $this;
    }

    public function start() : void
    {
        $this->update(['started_at' => now()]);
    }

    public function stop() : void
    {
        $this->update(['stopped_at' => now()]);
    }

    public function finished() : void
    {
        $this->update(['finished_at' => now()]);
    }

    public function failed() : void
    {
        $this->update(['failed_at' => now()]);
    }
}
