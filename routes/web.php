<?php

use Laxmidhar\LogLens\Http\Controllers\LogLensController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('loglens.route_prefix', 'logs'))
    ->middleware(config('loglens.middleware', ['web']))
    ->name('loglens.')
    ->group(function () {
        Route::get('/', [LogLensController::class, 'index'])->name('index');
        Route::post('/clear/{file?}', [LogLensController::class, 'clear'])->name('clear');
        Route::get('/download/{file?}', [LogLensController::class, 'download'])->name('download');
        Route::post('/upload', [LogLensController::class, 'upload'])->name('upload');
    });
