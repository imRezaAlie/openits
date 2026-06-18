<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google/status', [GoogleAuthController::class, 'status'])
    ->name('api.auth.google.status');

Route::middleware('google.login.enabled')->group(function () {
    Route::post('/auth/google/login', [GoogleAuthController::class, 'loginWithGoogle'])
        ->name('api.auth.google.login');
});
