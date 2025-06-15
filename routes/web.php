<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::post('__job-tracker', [\Jiannius\JobTracker\Controllers\JobTrackerController::class, 'get'])->name('__job-tracker');
    Route::get('__job-tracker/download/{id}', [\Jiannius\JobTracker\Controllers\JobTrackerController::class, 'download'])->name('__job-tracker.download');
    Route::delete('__job-tracker/{id}', [\Jiannius\JobTracker\Controllers\JobTrackerController::class, 'delete'])->name('__job-tracker.delete');
});
