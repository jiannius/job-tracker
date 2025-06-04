<?php

use Illuminate\Support\Facades\Route;

Route::prefix('__ipay88')->as('__ipay88')->withoutMiddleware('web')->group(function () {
    Route::post('redirect', [\App\Http\Controllers\Ipay88Controller::class, 'redirect'])->name('.redirect');
    Route::post('webhook', [\App\Http\Controllers\Ipay88Controller::class, 'webhook'])->name('.webhook');
    Route::get('checkout', [\Jiannius\Ipay88\Ipay88Controller::class, 'checkout'])->name('.checkout');
});
