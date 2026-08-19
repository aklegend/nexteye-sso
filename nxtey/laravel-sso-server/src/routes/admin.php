<?php

use Illuminate\Support\Facades\Route;
use Nxtey\SsoServer\Http\Controllers\Admin\OAuthClientController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('sso-server.admin.')->group(function () {
    Route::get('/clients', [OAuthClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [OAuthClientController::class, 'store'])->name('clients.store');
    Route::delete('/clients/{id}', [OAuthClientController::class, 'destroy'])->name('clients.destroy');
});