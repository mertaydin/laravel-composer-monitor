<?php

use Illuminate\Support\Facades\Route;
use MertAydin\ComposerMonitor\Http\Controllers\ComposerMonitorController;
use MertAydin\ComposerMonitor\Http\Middleware\AuthorizeDashboard;

Route::group([
    'prefix' => trim((string) config('composer-monitor.route.prefix', 'composer-monitor'), '/'),
    'as' => config('composer-monitor.route.name', 'composer-monitor.'),
    'middleware' => ['web', 'auth', AuthorizeDashboard::class],
], function () {
    Route::get('/', [ComposerMonitorController::class, 'index'])->name('index');
    Route::post('/check', [ComposerMonitorController::class, 'check'])->name('check');
    Route::post('/preferences', [ComposerMonitorController::class, 'preferences'])->name('preferences');
});
