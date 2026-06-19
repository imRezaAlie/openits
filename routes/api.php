<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LdapAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google/status', [GoogleAuthController::class, 'status'])
    ->name('api.auth.google.status');

Route::get('/auth/ldap/status', [LdapAuthController::class, 'status'])
    ->name('api.auth.ldap.status');

Route::middleware('google.login.enabled')->group(function () {
    Route::post('/auth/google/login', [GoogleAuthController::class, 'loginWithGoogle'])
        ->name('api.auth.google.login');
});

Route::middleware('ldap.login.enabled')->group(function () {
    Route::post('/auth/ldap', [LdapAuthController::class, 'login'])
        ->name('api.auth.ldap.login');
});
