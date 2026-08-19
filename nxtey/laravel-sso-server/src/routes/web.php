<?php

use Illuminate\Support\Facades\Route;
use Nxtey\SsoServer\Http\Controllers\SsoLogoutController;

Route::get('/sso/logout', [SsoLogoutController::class, 'logout'])->name('sso.logout');