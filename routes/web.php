<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::post('__job-tracker', \Jiannius\JobTracker\Controllers\JobTrackerController::class)->name('__job-tracker');
    Route::delete('__job-tracker/{id}', [\Jiannius\JobTracker\Controllers\JobTrackerController::class, 'delete'])->name('__job-tracker.delete');
});
