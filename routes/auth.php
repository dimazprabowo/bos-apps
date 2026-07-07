<?php

use App\Http\Controllers\Auth\SsoAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('register', 'auth.register')
        ->middleware('registration.open')
        ->name('register');

    Route::view('login', 'auth.login')
        ->name('login');

    Route::view('forgot-password', 'auth.forgot-password')
        ->name('password.request');

    Route::get('reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');
});

// SSO OAuth Routes - accessible regardless of auth status
// so re-authentication works when SSO user changes (e.g. impersonation)
if (config('services.sso.enabled')) {
    Route::get('auth/sso/redirect', [SsoAuthController::class, 'redirect'])->name('sso.redirect');
    Route::get('auth/callback', [SsoAuthController::class, 'callback'])->name('sso.callback');
}

Route::middleware('auth')->group(function () {
    Route::view('verify-email', 'auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::view('confirm-password', 'auth.confirm-password')
        ->name('password.confirm');
});
