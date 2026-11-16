<?php

use Illuminate\Support\Facades\Route;
use MertAydin\ComposerMonitor\Http\Controllers\ComposerMonitorController;

Route::group([
    'prefix' => config('composer-monitor.route.prefix', 'composer-monitor'),
    'as' => config('composer-monitor.route.name', 'composer-monitor.'),
    'middleware' => config('composer-monitor.route.middleware', ['web']),
], function () {
    Route::get('/', [ComposerMonitorController::class, 'index'])->name('index');
    Route::post('/check', [ComposerMonitorController::class, 'check'])->name('check');
    Route::post('/preferences', [ComposerMonitorController::class, 'preferences'])->name('preferences');
});
