<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;

// PUB ROUTE
Route::get('/sss', function () {
    return view('welcome');
});

// GUEST ROUTES
Route::middleware('guest')->controller(AuthController::class)->group(function() {
    Route::get('/register', 'showRegister')->name('show.register');
    Route::get('/', 'showLogin')->name('show.login');
    Route::post('/register', 'register')->name('register');
    Route::post('/login', 'login')->name('login');
    Route::get('/login', 'showLogin')->name('login');
});



// PASS RESET ROUTES
Route::controller(ForgotPasswordController::class)->group(function() {
    Route::get('/forgot-password', 'showLinkRequestForm')->name('password.request');
    Route::post('/forgot-password', 'sendResetLinkEmail')->name('password.email');
});

Route::controller(ResetPasswordController::class)->group(function() {
    Route::get('/reset-password/{token}', 'showResetForm')->name('password.reset');
    Route::post('/reset-password', 'reset')->name('password.update');
});



// AUTH ROUTES
Route::middleware('auth')->group(function() {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/email/verify', function () {
        return view('auth.verify-email')->with('status', 'Verification email sent! Please check your Gmail.');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('show.home');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function () {
        auth()->user()->sendEmailVerificationNotification();
        return back()->with('status', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');

});



// USER ROUTES
Route::middleware(['auth', 'verified'])->controller(MainController::class)->group(function() {
    Route::get('/res/home', 'showDashboard')->name('show.home');
    Route::get('/res/create', 'showCreate')->name('show.create');
    Route::get('/res/verify', 'showVerify')->name('show.verify');
    Route::get('/res/files', 'showFiles')->name('show.files');
});


// ADMIN ROUTES
Route::middleware(['auth', 'admin'])->controller(AdminController::class)->group(function() {
    Route::get('/admin/home', 'showDashboard')->name('admin.home');
});