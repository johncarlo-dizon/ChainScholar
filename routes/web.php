<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminTitleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResearchPaperController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TitleController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;





Route::post('/documents/check-plagiarism', [DocumentController::class, 'checkPlagiarismLive'])->name('documents.checkPlagiarismLive');




Route::get('/dashboard', [DocumentController::class, 'showSearchDashboard'])->name('dashboard');
Route::post('/dashboard/search', [DocumentController::class, 'searchResearch'])->name('dashboard.search');
Route::get('/dashboard/view/{id}', [DocumentController::class, 'viewResearch'])->name('dashboard.view');
 




Route::prefix('admin/titles')->name('admin.titles.')->group(function () {
    Route::get('/pending', [AdminTitleController::class, 'pendingTitles'])->name('pending');
    Route::get('/approved', [AdminTitleController::class, 'approvedTitles'])->name('approved');
    Route::patch('/{id}/approve', [AdminTitleController::class, 'approve'])->name('approve');
    Route::patch('/return', [AdminTitleController::class, 'return'])->name('return');
});
Route::get('/admin/documents/{id}/review', [AdminTitleController::class, 'review'])->name('admin.documents.review');







// Admin view of final document
Route::get('/admin/documents/{id}/view', [AdminTitleController::class, 'viewFinal'])->name('admin.documents.view');



Route::patch('/titles/{id}/cancel', [DocumentController::class, 'cancelSubmission'])->name('titles.cancel');
Route::get('/submitted-documents', [DocumentController::class, 'showSubmittedDocuments'])->name('documents.submitted');
Route::get('/documents/{id}/view', [DocumentController::class, 'viewFinalDocument'])->name('documents.view');

Route::post('/documents/submit/{title_id}', [DocumentController::class, 'submitFinal'])->name('documents.submit');







Route::middleware(['auth'])->group(function () {
    Route::get('/submit-research', [ResearchPaperController::class, 'create'])
        ->name('research-papers.create');
    Route::post('/submit-research', [ResearchPaperController::class, 'store'])
        ->name('research-papers.store');
    Route::get('/check-filename', [ResearchPaperController::class, 'checkFilename'])
        ->name('research-papers.check-filename');
});






Route::post('/documents/combine/custom/{titleId}', [DocumentController::class, 'combineCustom'])->name('documents.combine.custom');
Route::get('/documents/{document}/undo-template', [DocumentController::class, 'undoTemplate'])->name('documents.undoTemplate');
Route::get('/clear-template-session', function () {
    session()->forget('templateContent');
    session()->forget('previousEditorContent');
    return response()->json(['cleared' => true]);
})->name('clear.template.session');



 


Route::get('/get-template-content/{id}', function ($id) {
    $template = \App\Models\Template::findOrFail($id);
    return response()->json(['content' => $template->content]);
})->middleware('auth');

 


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
    Route::get('/titles', [TitleController::class, 'index'])->name('titles.index');
    Route::delete('/titles/{id}', [TitleController::class, 'destroy'])->name('titles.destroy');
    Route::get('/documents/verify', [TitleController::class, 'verifyForm'])->name('titles.verify');

// Handle POST from verify form and redirect to chapters (step 2)
    Route::post('/documents/verify', [TitleController::class, 'verifyAndProceed'])->name('titles.verify.submit');




// Show chapters page after verifying title (step 2)
    Route::get('/titles/{id}/chapters', [TitleController::class, 'showChapters'])->name('titles.chapters');
    Route::get('/open/{id}/chapters', [TitleController::class, 'showChapters'])->name('open.chapters');




    Route::resource('documents', DocumentController::class);
    Route::resource('templates', TemplateController::class);
    Route::get('/templates/{template}/use', [TemplateController::class, 'useTemplate'])->name('templates.use');
    Route::post('/upload-image', [DocumentController::class, 'uploadImage'])
        ->name('upload.image');
    Route::get('show/verify' , [DocumentController::class, 'showVerify'])->name('show.verify');

});



Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update-avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update.avatar');
    Route::post('/profile/update-username', [ProfileController::class, 'updateUsername'])->name('profile.update.username');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
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