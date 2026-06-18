<?php

use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google/status', [GoogleAuthController::class, 'status'])
    ->name('auth.google.status');

Route::middleware('google.login.enabled')->group(function () {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])
        ->name('auth.google.redirect');

    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])
        ->name('auth.google.callback');

    Route::post('/auth/google/login', [GoogleAuthController::class, 'loginWithGoogle'])
        ->name('auth.google.login');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'settings.manage'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/google', [SettingsController::class, 'updateGoogleSettings'])->name('settings.google.update');
});
