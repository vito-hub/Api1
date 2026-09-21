<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\OAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('auth.login');
//});
//
//Route::post('/login', [AuthController::class, 'checkPhone'])->name('login');
//
//Route::get('/oauth/callback', [AuthController::class, 'callback']);
//
//Route::middleware('verify.passport')->get('/dashboard', function (Request $request) {
//    return 'Logged in as user ' . $request->attributes->get('auth_user_id');
//});
//
//Route::get('/login/password', function () {
//    if (! session('pending_phone')) {
//        return redirect('/login');
//    }
//    return view('auth.password');
//});
//
//Route::post('/login/password', [AuthController::class, 'verifyPassword'])->name('verify-password');

Route::get('/', [OAuthController::class, 'redirect'])
    ->name('login');
Route::get('/logout', [OAuthController::class, 'logOut'])
    ->name('logOut');

Route::get('/oauth/callback', [OAuthController::class, 'callback'])
    ->name('oauth.callback');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [OauthController::class, 'dashboard'])->name('dashboard');
    Route::get('/test', [OauthController::class, 'getUserData'])->name('test');

});

Route::webhooks('myWebhook', 'user-created');
Route::webhooks('webhook-user-updated', 'user-updated');

