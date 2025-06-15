<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplateController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;



//TITLE VERIFY
Route::post('/check-title-similarity', [DocumentController::class, 'checkTitleSimilarity'])->name('documents.check-similarity');
Route::post('/check-title-web', [DocumentController::class, 'checkWebTitleSimilarity'])->name('documents.check-web');





//NOTIF
Route::post('/notifications/read/{id}', function ($id) {
    $notif = \App\Models\Notification::where('id', $id)
              ->where('user_id', auth()->id())
              ->firstOrFail();

    $notif->update([
        'is_read' => true,
        'read_at' => now(),
    ]);

    return response()->json(['success' => true]);
});

 

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update-avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update.avatar');
    Route::post('/profile/update-username', [ProfileController::class, 'updateUsername'])->name('profile.update.username');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
});

 




Route::middleware(['auth'])->group(function () {
    Route::resource('documents', DocumentController::class);
    Route::resource('templates', TemplateController::class);
    Route::get('/templates/{template}/use', [TemplateController::class, 'useTemplate'])->name('templates.use');
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
    Route::get('/admin/index', [UserController::class, 'showDashboard'])->name('admin.index');
    
    // User CRUD Routes
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});