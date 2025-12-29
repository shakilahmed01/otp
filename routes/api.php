<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// OTP API routes (authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/otp/verify', [\App\Http\Controllers\Auth\OtpController::class, 'apiVerify']);
    Route::post('/otp/resend', [\App\Http\Controllers\Auth\OtpController::class, 'apiResend']);
});

// Public API registration
Route::post('/register', [\App\Http\Controllers\Api\Auth\RegisterController::class, 'register']);

// Public API login
Route::post('/login', [\App\Http\Controllers\Api\Auth\LoginController::class, 'login']);
