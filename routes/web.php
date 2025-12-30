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

// Payment routes
Route::middleware('auth')->group(function () {
    Route::get('/payment/create', [App\Http\Controllers\PaymentController::class, 'create'])->name('payment.create');
    Route::post('/payment/init', [App\Http\Controllers\PaymentController::class, 'initPayment'])->name('payment.init');
    Route::get('/payment', [App\Http\Controllers\PaymentController::class, 'index'])->name('payment.index');
    Route::get('/payment/{id}', [App\Http\Controllers\PaymentController::class, 'show'])->name('payment.show');
});

// Payment callbacks (no auth required as they come from SSL Commerce)
// These routes accept both GET and POST as SSL Commerce may use either method
Route::match(['get', 'post'], '/payment/ipn', [App\Http\Controllers\PaymentController::class, 'ipn'])->name('payment.ipn');
Route::match(['get', 'post'], '/payment/success', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
Route::match(['get', 'post'], '/payment/fail', [App\Http\Controllers\PaymentController::class, 'fail'])->name('payment.fail');
Route::match(['get', 'post'], '/payment/cancel', [App\Http\Controllers\PaymentController::class, 'cancel'])->name('payment.cancel');
