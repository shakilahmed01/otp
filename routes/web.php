<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// OTP verification routes
Route::middleware('auth')->group(function () {
    Route::get('/otp/verify', [\App\Http\Controllers\Auth\OtpController::class, 'showVerifyForm'])->name('otp.verify.form');
    Route::post('/otp/verify', [\App\Http\Controllers\Auth\OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/resend', [\App\Http\Controllers\Auth\OtpController::class, 'resend'])->name('otp.resend');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('otp.verified');
