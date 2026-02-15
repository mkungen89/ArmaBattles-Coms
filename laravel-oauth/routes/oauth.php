<?php

use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OAuth Routes
|--------------------------------------------------------------------------
|
| These routes handle OAuth 2.0 authentication for third-party applications
| like Arma Battles Chat.
|
*/

// OAuth Authorization Flow
Route::prefix('oauth')->group(function () {
    // Authorization endpoint - shows consent page
    Route::get('/authorize', [OAuthController::class, 'authorize'])
        ->name('oauth.authorize.show');

    // Handle authorization approval/denial
    Route::post('/authorize', [OAuthController::class, 'approveAuthorization'])
        ->middleware('auth')
        ->name('oauth.authorize');

    // Token endpoint - exchange code for access token
    Route::post('/token', [OAuthController::class, 'issueToken'])
        ->name('oauth.token');

    // User info endpoint - get authenticated user data
    Route::get('/user', [OAuthController::class, 'userInfo'])
        ->middleware('oauth')
        ->name('oauth.user');

    // Token revocation endpoint
    Route::post('/revoke', [OAuthController::class, 'revokeToken'])
        ->name('oauth.revoke');
});
