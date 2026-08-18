<?php

use Illuminate\Support\Facades\Route;
use Nxtey\SsoClient\Http\Controllers\SsoController;

Route::prefix('sso')->name('sso.')->group(function () {
    Route::get('/login', [SsoController::class, 'redirect'])->name('login');
    Route::get('/callback', [SsoController::class, 'callback'])->name('callback');
    Route::get('/logout', [SsoController::class, 'logout'])->name('logout');
});