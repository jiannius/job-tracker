<?php

namespace Jiannius\JobTracker;;

use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class JobTrackerServiceProvider extends ServiceProvider
{
    // register
    public function register() : void
    {
        //
    }

    // boot
    public function boot() : void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        /**
         * Listen for queued event
         *
         * If the project uses Horizon, we will listen to the JobPushed event,
         * because Horizon fires JobPushed event when the job is queued or retry the job again from its UI.
         *
         * @see https://laravel.com/docs/horizon
         */
        $queued = class_exists('Laravel\Horizon\Events\JobPushed')
            ? 'Laravel\Horizon\Events\JobPushed'
            : JobQueued::class;

        Event::listen($queued, function ($event) {
            app(JobTracker::class)->queued($event);
        });

        /**
         * Listen for other events
         */
        $manager = app(QueueManager::class);

        $manager->before(static function (JobProcessing $event) {
            app(JobTracker::class)->running($event);
        });

        $manager->after(static function (JobProcessed $event) {
            app(JobTracker::class)->finished($event);
        });

        $manager->failing(static function (JobFailed $event) {
            app(JobTracker::class)->failed($event);
        });

        $manager->exceptionOccurred(static function (JobExceptionOccurred $event) {
            app(JobTracker::class)->exception($event);
        });
    }
}