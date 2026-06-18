<?php

use App\Http\Controllers\Admin\AdminLdapController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LdapAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google/status', [GoogleAuthController::class, 'status'])
    ->name('auth.google.status');

Route::get('/auth/ldap/status', [LdapAuthController::class, 'status'])
    ->name('auth.ldap.status');

Route::middleware('google.login.enabled')->group(function () {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])
        ->name('auth.google.redirect');

    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])
        ->name('auth.google.callback');

    Route::post('/auth/google/login', [GoogleAuthController::class, 'loginWithGoogle'])
        ->name('auth.google.login');
});

Route::middleware('ldap.login.enabled')->group(function () {
    Route::post('/auth/ldap/login', [LdapAuthController::class, 'login'])
        ->name('auth.ldap.login');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'settings.manage'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/google', [SettingsController::class, 'updateGoogleSettings'])->name('settings.google.update');

    Route::get('/settings/ldap', [AdminLdapController::class, 'index'])->name('settings.ldap');
    Route::put('/settings/ldap', [AdminLdapController::class, 'update'])->name('settings.ldap.update');
    Route::post('/ldap/test', [AdminLdapController::class, 'test'])->name('ldap.test');
    Route::post('/ldap/sync', [AdminLdapController::class, 'sync'])->name('ldap.sync');
    Route::post('/ldap/toggle', [AdminLdapController::class, 'toggle'])->name('ldap.toggle');
});
