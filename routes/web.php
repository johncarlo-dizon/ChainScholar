<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;
 

Route::middleware(['auth'])->group(function () {
    Route::resource('documents', DocumentController::class);
 
    Route::post('/upload-image', [DocumentController::class, 'uploadImage'])
        ->name('upload.image');
    Route::get('show/verify' , [DocumentController::class, 'showVerify'])->name('show.verify');
    Route::get('show/dashboard' , [DocumentController::class, 'showDashboard'])->name('show.dashboard');
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





// ADMIN ROUTES
Route::middleware(['auth', 'admin'])->group(function() {
    // Admin Dashboard
    Route::get('/admin/home', [AdminController::class, 'showDashboard'])->name('admin.home');
    
    // User CRUD Routes
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});