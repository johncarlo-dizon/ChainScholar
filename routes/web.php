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
use App\Http\Controllers\TitleVerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AdviserController;
use App\Http\Controllers\PlagiarismController;
use App\Models\Document;
use App\Http\Controllers\PdfPlagiarismController;





// PDF ADMIN AND USER DASH
Route::middleware(['auth'])->group(function () {
    Route::get('/my-research-papers', [ResearchPaperController::class, 'viewStudentPdf'])
        ->name('research-papers.student-index');
});

// Admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/research-papers', [ResearchPaperController::class, 'viewAdminPdf'])
        ->name('research-papers.admin-index');
    Route::delete('/admin/research-papers/{researchPaper}', [ResearchPaperController::class, 'destroyAdminPdf'])
        ->name('research-papers.destroy');
});


//PDF PLAG  
Route::post('/research-papers/check-plagiarism', [PdfPlagiarismController::class, 'checkPdfPlagiarismLive'])
    ->name('research-papers.check-plagiarism');

Route::post('/research-papers/check-plagiarism-detailed', [PdfPlagiarismController::class, 'checkPdfPlagiarismDetailed'])
    ->name('research-papers.check-plagiarism-detailed');

    
Route::middleware(['auth'])->group(function () {
    Route::get('/submit-research', [ResearchPaperController::class, 'create'])
        ->name('research-papers.create');
    Route::post('/submit-research', [ResearchPaperController::class, 'store'])
        ->name('research-papers.store');
    Route::get('/check-filename', [ResearchPaperController::class, 'checkFilename'])
        ->name('research-papers.check-filename');
});






//TITLE VERIFY
Route::middleware(['auth'])->group(function () {
    Route::post('/check-title-similarity', [TitleVerificationController::class, 'checkTitleSimilarity'])
        ->name('documents.check-similarity');
    Route::post('/check-title-web', [TitleVerificationController::class, 'checkWebTitleSimilarity'])
        ->name('documents.check-web');
});

 



 

Route::middleware(['auth','student'])->group(function () {
    Route::get('/titles/awaiting', [TitleController::class, 'showAwaitingTitles'])
        ->name('titles.awaiting');

    Route::post('/titles/{title}/adviser/change', [TitleController::class, 'changeAdviser'])
        ->name('titles.adviser.change');

    Route::post('/titles/{title}/adviser/cancel', [TitleController::class, 'cancelAdviserRequest'])
        ->name('titles.adviser.cancel');

    // Student accepts / declines incoming adviser-initiated requests
    Route::post('/titles/{title}/incoming/{adviserRequest}/accept', [TitleController::class, 'acceptIncoming'])
        ->name('titles.incoming.accept');

    Route::post('/titles/{title}/incoming/{adviserRequest}/decline', [TitleController::class, 'declineIncoming'])
        ->name('titles.incoming.decline');
});



Route::post('/documents/check-plagiarism', [PlagiarismController::class, 'checkPlagiarismLive'])->name('documents.checkPlagiarismLive');
Route::post('/documents/check-plagiarism-detailed', [PlagiarismController::class, 'checkPlagiarismDetailed'])
    ->name('documents.checkPlagiarismDetailed');


Route::get('/dashboard', [DocumentController::class, 'showSearchDashboard'])->name('dashboard');
Route::post('/dashboard/search', [DocumentController::class, 'searchResearch'])->name('dashboard.search');
Route::get('/dashboard/view/{id}', [DocumentController::class, 'viewResearch'])->name('dashboard.view');
 




Route::middleware(['auth','admin'])
    ->prefix('admin/titles')
    ->name('admin.titles.')
    ->group(function () {
        // List all submitted titles (read-only)
        Route::get('/submitted', [AdminTitleController::class, 'submittedTitles'])
            ->name('submitted');

        // View a submitted (final) document
        Route::get('/submitted/view/{document}', [AdminTitleController::class, 'viewSubmittedDocument'])
            ->name('submitted.view');
    });

// (Optional) temporary compatibility redirect if old links still hit this route:
Route::get('/admin/documents/{document}/review', function (Document $document) {
    return redirect()->route('admin.titles.submitted.view', $document);
})->name('admin.documents.review');






// Admin view of final document
Route::get('/admin/documents/{id}/view', [AdminTitleController::class, 'viewFinal'])->name('admin.documents.view');



Route::patch('/titles/{id}/cancel', [DocumentController::class, 'cancelSubmission'])->name('titles.cancel');
Route::get('/submitted-documents', [DocumentController::class, 'showSubmittedDocuments'])->name('documents.submitted');
Route::get('/documents/{id}/view', [DocumentController::class, 'viewFinalDocument'])->name('documents.view');

Route::post('/documents/submit/{title_id}', [DocumentController::class, 'submitFinal'])->name('documents.submit');











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

 




Route::middleware('auth')->group(function () { 
    // LIST older notifications for the sidebar loader
    Route::get('/notifications/list', function (\Illuminate\Http\Request $request) {
        $after = (int) $request->query('after', 0); // last id currently shown (optional)
        $limit = min((int)$request->query('limit', 10), 30); // hard cap

        $q = \App\Models\Notification::where('user_id', auth()->id());

        if ($after > 0) {
            // fetch OLDER (smaller id) than the last item the client has
            $q->where('id', '<', $after);
        }

        $items = $q->orderByDesc('id')
                ->limit($limit)
                ->get(['id','title','message','is_read','created_at']);

        return response()->json([
            'items' => $items->map(fn($n) => [
                'id'        => $n->id,
                'title'     => $n->title,
                'message'   => $n->message,
                'is_read'   => (bool)$n->is_read,
                'created_at'=> $n->created_at->diffForHumans(),
            ]),
        ]);
    })->name('notifications.list');

    // Your existing mark-as-read route (unchanged)
    Route::post('/notifications/read/{id}', function ($id) {
        $notif = \App\Models\Notification::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

        if (!$notif->is_read) {
            $notif->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    })->name('notifications.read');

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


Route::middleware(['auth', 'adviser'])
    ->prefix('adviser')
    ->name('adviser.')
    ->group(function () {
        Route::get('/', [AdviserController::class, 'index'])->name('index');

        // Browse titles to request
        Route::get('/titles/browse', [AdviserController::class, 'browse'])->name('titles.browse');
        Route::post('/titles/{title}/request', [AdviserController::class, 'requestToAdvise'])->name('titles.request');

        // Pending requests addressed to this adviser
        Route::get('/requests/pending', [AdviserController::class, 'pending'])->name('requests.pending');
        Route::post('/requests/{adviserRequest}/accept', [AdviserController::class, 'accept'])->name('requests.accept');
        Route::post('/requests/{adviserRequest}/decline', [AdviserController::class, 'decline'])->name('requests.decline');


        Route::get('/advised', [AdviserController::class, 'listAdvisedTitles'])
            ->name('advised.index');
        Route::get('/advised/{title}', [AdviserController::class, 'viewAdvisedTitle'])
            ->name('advised.show');
        Route::get('/advised/{title}/chapters/{document}', [AdviserController::class, 'showAdvisedChapter'])
            ->name('advised.chapter.show');
            Route::post('/advised/{title}/chapters/{document}/note',
            [AdviserController::class, 'saveChapterNote']
        )->name('advised.chapter.note.save');
    });