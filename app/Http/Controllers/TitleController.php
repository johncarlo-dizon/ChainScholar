<?php

namespace App\Http\Controllers;
use App\Models\Title;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Notification;
use App\Models\AdviserRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class TitleController extends Controller
{
    //
    

    public function verifyForm(){  // NAV - DOC.VERIFY
    $advisers = \App\Models\User::where('role', 'ADVISER')
        ->orderBy('name')
        ->get(['id','name','department','specialization']);

    return view('documents.verify', compact('advisers'));
    }


  
    public function verifyAndProceed(Request $request)
    {
    $data = $request->validate([
        'title'       => 'required|string|max:255',
        'adviser_id'  => 'required|exists:users,id',
    ]);

    // Ensure the chosen user is actually an ADVISER
    $adviser = User::where('id', $data['adviser_id'])
        ->where('role', 'ADVISER')
        ->first();

    if (! $adviser) {
        return back()->withErrors(['adviser_id' => 'Selected adviser is invalid.'])->withInput();
    }

    $title = null;

    DB::transaction(function () use ($data, $adviser, &$title) {
        // Create the title (already “verified” client-side) → awaiting adviser
        $title = Title::create([
            'owner_id'     => auth()->id(),    // ← new schema column (owner_id)
            'title'        => $data['title'],
            'status'       => 'awaiting_adviser',
            'submitted_at' => now(),
            'verified_at'  => now(),
        ]);

        // Default chapters
        foreach (['Chapter 1','Chapter 2','Chapter 3','Chapter 4','Chapter 5'] as $chapterName) {
            Document::create([
                'user_id'  => auth()->id(),
                'title_id' => $title->id,
                'chapter'  => $chapterName,
                'content'  => '',
                'format'   => 'separate',
            ]);
        }

        // Create a pending adviser request (student-initiated)
        AdviserRequest::create([
            'title_id'     => $title->id,
            'adviser_id'   => $adviser->id,
            'requested_by' => 'student',
            'status'       => 'pending',
            'message'      => null,
        ]);

        // Notifications
        Notification::create([
            'user_id' => auth()->id(),
            'title'   => 'Title Verified',
            'message' => 'Your title and default chapters were created. Adviser request sent to '.$adviser->name.'.',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $adviser->id,
            'title'   => 'New Adviser Request',
            'message' => auth()->user()->name.' requested you to advise the title: "'.$title->title.'".',
            'is_read' => false,
        ]);
    });

    return redirect()
        ->route('titles.awaiting', $title->id)
        ->with('status', 'Title created and adviser request sent!');
    }





    public function showChapters($titleId)
    {
        $title = Title::with('documents')->findOrFail($titleId);
        return view('documents.chapters', compact('title'));
    }


    public function index()
    {
        $titles = auth()->user()
            ->titles()
       ->whereIn('status', ['in_advising'])
            ->latest()
            ->get();
    
        return view('documents.index', compact('titles'));
    }
    
    
    

    public function destroy($id)
    {
    $title = Title::with('documents')->where('user_id', auth()->id())->findOrFail($id);

    // Delete related chapters/documents
    $title->documents()->delete();
    
    // Delete title
    $title->delete();

    return redirect()->route('titles.index')->with('status', 'Title and its chapters deleted successfully.');
    }


      public function showAwaitingTitles(Request $request)
    {
        $userId = $request->user()->id;

        $titles = Title::query()
            ->with([
                // Load ALL pending requests (both student/adviser)
                'adviserRequests' => fn ($q) => $q->where('status', 'pending')->with('adviser'),
            ])
            ->where('owner_id', $userId)                 // use 'user_id' if you haven't migrated yet
            ->where('status', 'awaiting_adviser')
            ->whereNull('primary_adviser_id')
            ->orderByDesc('submitted_at')
            ->paginate(10);

        $advisers = User::where('role', 'ADVISER')
            ->orderBy('name')
            ->get(['id','name','department','specialization']);

        return view('documents.awaiting', compact('titles', 'advisers'));
    }

    /** Change adviser choice (withdraw student request, create a new student request) */
 public function changeAdviser(Request $request, Title $title)
{
    $data = $request->validate(['adviser_id' => 'required|exists:users,id']);
    $user = $request->user();
    abort_if($title->owner_id !== $user->id, 403);

    if ($title->primary_adviser_id || $title->status !== 'awaiting_adviser') {
        return back()->with('error', 'This title is not eligible for changing adviser.');
    }

    $newAdviser = User::where('id', $data['adviser_id'])
        ->where('role', 'ADVISER')
        ->first();
    if (!$newAdviser) {
        return back()->with('error', 'Selected adviser is invalid.');
    }

    DB::transaction(function () use ($title, $newAdviser, $user) {
        // 1) If there is ANY existing PENDING for this (title, adviser), reuse it.
        $existingPending = AdviserRequest::where('title_id', $title->id)
            ->where('adviser_id', $newAdviser->id)
            ->where('status', 'pending')      // note: no filter on requested_by
            ->first();

        if ($existingPending) {
            // Optional: attribute it to student now
            if ($existingPending->requested_by !== 'student') {
                $existingPending->requested_by = 'student';
                $existingPending->save();
            }

            // Remove other student PENDING requests to different advisers (no toggling to withdrawn)
            AdviserRequest::where('title_id', $title->id)
                ->where('requested_by', 'student')
                ->where('status', 'pending')
                ->where('adviser_id', '<>', $newAdviser->id)
                ->delete();

            // Notify and return
            if (class_exists(Notification::class)) {
                Notification::create([
                    'user_id' => $user->id,
                    'title'   => 'Adviser Request Kept',
                    'message' => "You’re already pending with {$newAdviser->name} for \"{$title->title}\".",
                    'is_read' => false,
                ]);
            }
            return;
        }

        // 2) No pending row yet for this adviser → clean up other student pendings by deleting them
        AdviserRequest::where('title_id', $title->id)
            ->where('requested_by', 'student')
            ->where('status', 'pending')
            ->where('adviser_id', '<>', $newAdviser->id)
            ->delete();

        // 3) Upsert a single row for this (title, adviser) to PENDING
        //    (don’t include 'status' in the lookup so we can revive withdrawn/declined rows)
        $req = AdviserRequest::where('title_id', $title->id)
            ->where('adviser_id', $newAdviser->id)
            ->orderByDesc('id')
            ->first();

        if (!$req) {
            $req = new AdviserRequest([
                'title_id'   => $title->id,
                'adviser_id' => $newAdviser->id,
            ]);
        }

        $req->requested_by = 'student';
        $req->status       = 'pending';
        $req->decided_at   = null;
        $req->save();

        if (class_exists(Notification::class)) {
            Notification::create([
                'user_id' => $user->id,
                'title'   => 'Adviser Request Sent',
                'message' => "You requested {$newAdviser->name} to advise \"{$title->title}\".",
                'is_read' => false,
            ]);

            Notification::create([
                'user_id' => $newAdviser->id,
                'title'   => 'New Adviser Request',
                'message' => "{$user->name} requested you to advise: \"{$title->title}\".",
                'is_read' => false,
            ]);
        }
    });

    return back()->with('success', 'Adviser request updated.');
}


    /** Student ACCEPTS an incoming adviser-initiated request */
    public function acceptIncoming(Request $request, Title $title, AdviserRequest $adviserRequest)
    {
        $user = $request->user();
        abort_if($title->owner_id !== $user->id, 403);
        abort_if($adviserRequest->title_id !== $title->id, 404);

        if ($adviserRequest->requested_by !== 'adviser' || $adviserRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }
        if ($title->primary_adviser_id) {
            return back()->with('error', 'A primary adviser is already assigned.');
        }

        DB::transaction(function () use ($title, $adviserRequest) {
            // Accept the chosen request
            $adviserRequest->update([
                'status'     => 'accepted',
                'decided_at' => now(),
            ]);

            // Assign adviser to title
            $title->update([
                'primary_adviser_id'  => $adviserRequest->adviser_id,
                'adviser_assigned_at' => now(),
                'status'              => 'in_advising',
            ]);

            // Close all other pending requests (student/adviser)
            AdviserRequest::where('title_id', $title->id)
                ->where('id', '!=', $adviserRequest->id)
                ->where('status', 'pending')
                ->update(['status' => 'declined', 'decided_at' => now()]);
        });

        return back()->with('success', 'Adviser accepted. Title is now in advising.');
    }

    /** Student DECLINES an incoming adviser-initiated request */
    public function declineIncoming(Request $request, Title $title, AdviserRequest $adviserRequest)
    {
        $user = $request->user();
        abort_if($title->owner_id !== $user->id, 403);
        abort_if($adviserRequest->title_id !== $title->id, 404);

        if ($adviserRequest->requested_by !== 'adviser' || $adviserRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        $adviserRequest->update([
            'status'     => 'declined',
            'decided_at' => now(),
        ]);

        return back()->with('success', 'Adviser request declined.');
    }

    /** Withdraw your pending student-initiated request */
    public function cancelAdviserRequest(Request $request, Title $title)
    {
        $user = $request->user();
        abort_if($title->owner_id !== $user->id, 403);

        if ($title->status !== 'awaiting_adviser' || $title->primary_adviser_id) {
            return back()->with('error', 'This title is not eligible for cancellation.');
        }

        // Delete pending student requests to avoid unique collisions on 'withdrawn'
        $count = AdviserRequest::where('title_id', $title->id)
            ->where('requested_by', 'student')
            ->where('status', 'pending')
            ->delete();

        return back()->with('success', $count
            ? 'Your pending adviser request was withdrawn.'
            : 'No pending student request to withdraw.');
    }



}
