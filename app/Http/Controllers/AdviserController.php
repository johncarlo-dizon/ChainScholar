<?php

namespace App\Http\Controllers;

use App\Models\AdviserRequest;
use App\Models\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Document;
use App\Models\AdviserNote; 
class AdviserController extends Controller
{
    /**
     * Adviser dashboard: quick stats + shortcuts.
     */

    // Add this method to your AdviserController

/** Adviser cancels their pending request */
public function cancelAdviserRequest(Request $request, AdviserRequest $adviserRequest)
{
    $user = $request->user();

 

    if ($adviserRequest->status !== 'pending') {
        return back()->with('error', 'This request is no longer pending.');
    }

    // Check if title is in awaiting_admin status (not allowed to cancel)
    $title = $adviserRequest->title;
    if ($title->status === 'awaiting_admin') {
        return back()->with('error', 'Cannot cancel request when title is awaiting admin approval.');
    }

    DB::transaction(function () use ($adviserRequest, $user) {
        $title = $adviserRequest->title;

        // Update request status to 'withdrawn'
        $adviserRequest->update([
            'status' => 'withdrawn',
            'decided_at' => now(),
        ]);

        // Notify the student
        if (class_exists(\App\Models\Notification::class)) {
            \App\Models\Notification::create([
                'user_id' => $title->owner_id,
                'title' => 'Adviser Request Cancelled',
                'message' => $user->name . ' cancelled their request to advise your title "'.$title->title.'".',
                'is_read' => false,
            ]);
        }

        // Notify the adviser
        if (class_exists(\App\Models\Notification::class)) {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'title' => 'Request Cancelled',
                'message' => 'Your request to advise "'.$title->title.'" has been cancelled.',
                'is_read' => false,
            ]);
        }
    });

    return back()->with('status', 'Request cancelled successfully.');
}
    public function index(Request $request)
    {
        $user = $request->user();

       $pendingCount = AdviserRequest::where('adviser_id', $user->id)
        ->where('requested_by', 'student')
        ->where('status', 'pending')
        ->whereNull('decided_at')
        ->count();

        $myAdvisedCount = Title::where('primary_adviser_id', $user->id)->count();

        $myPendingSent = AdviserRequest::where('adviser_id', $user->id)
        ->where('requested_by', 'adviser')
        ->where('status', 'pending')
        ->whereNull('decided_at')
        ->with('title.owner')
        ->latest()
        ->take(5)
        ->get();


            $incomingRequests = AdviserRequest::where('adviser_id', $user->id)
        ->where('requested_by', 'student')   // ← only items you should respond to
        ->where('status', 'pending')
        ->whereNull('decided_at')
        ->with('title.owner')
        ->latest()
        ->take(5)
        ->get();


        return view('adviser.index', compact(
            'pendingCount',
            'myAdvisedCount',
            'myPendingSent',
            'incomingRequests'
        ));
    }

    /**
     * Browse titles an adviser can request to advise.
     * Shows titles that are verified/awaiting_adviser, have no primary adviser yet,
     * and where THIS adviser doesn't already have a pending/accepted request.
     */
    // AdviserController@browse
   public function browse(Request $request)
    {
        $user = $request->user();

        $status  = $request->string('status')->toString() ?: 'all';      // all|verified|awaiting_adviser|awaiting_admin
        $assign  = $request->string('assign')->toString() ?: 'all';      // all|unassigned|assigned
        $orderBy = $request->string('sort')->toString()   ?: 'verified'; // verified|title|owner

        $titles = Title::query()
            // Always show these 3 states
            ->whereIn('status', ['verified','awaiting_adviser','awaiting_admin'])
            // Search
            ->when($request->filled('search'), function ($qq) use ($request) {
                $s = $request->string('search')->toString();
                $qq->where(function ($w) use ($s) {
                    $w->where('title', 'like', "%{$s}%")
                    ->orWhere('keywords', 'like', "%{$s}%")
                    ->orWhere('category', 'like', "%{$s}%")
                    ->orWhere('sub_category', 'like', "%{$s}%");
                });
            })
            // Status filter
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            // Assignment filter
            ->when($assign === 'unassigned', fn ($q) => $q->whereNull('primary_adviser_id'))
            ->when($assign === 'assigned', fn ($q) => $q->whereNotNull('primary_adviser_id'))
            // Flags for this adviser
           // Only requests YOU initiated (adviser → student)
->withExists(['adviserRequests as has_my_pending_request' => function ($r) use ($user) {
    $r->where('adviser_id', $user->id)
      ->where('requested_by', 'adviser')
      ->where('status', 'pending')
      ->whereNull('decided_at');
}])
->withExists(['adviserRequests as has_my_accepted_request' => function ($r) use ($user) {
    $r->where('adviser_id', $user->id)
      ->where('requested_by', 'adviser')
      ->where('status', 'accepted');
}])
// Student invited YOU (student → adviser)
->withExists(['adviserRequests as has_student_pending_invite' => function ($r) use ($user) {
    $r->where('adviser_id', $user->id)
      ->where('requested_by', 'student')
      ->where('status', 'pending')
      ->whereNull('decided_at');
}])

            ->with(['owner','primaryAdviser']) // to show "Assigned to: ..."
            // Sorting
            ->when($orderBy === 'title', fn ($q) => $q->orderBy('title'))
            ->when($orderBy === 'owner', fn ($q) => $q->orderBy(
                \DB::raw("(select name from users where users.id = titles.owner_id)")))
            ->when($orderBy === 'verified', fn ($q) => $q->orderByDesc('verified_at'))
            ->paginate(10)
            ->withQueryString();

        return view('adviser.browse', compact('titles'));
    }



    /**
     * View requests addressed to this adviser that are still pending.
     * Includes both student-initiated and adviser-initiated requests.
     */
    public function pending(Request $request)
    {
        $user = $request->user();

        $requests = AdviserRequest::with(['title.owner'])
    ->where('adviser_id', auth()->id())
    ->whereNull('decided_at')
    ->where('requested_by', 'student') // show only items you should respond to
    ->orderByDesc('created_at')
    ->paginate(10);


        return view('adviser.pending', compact('requests'));
    }

    /**
     * Adviser creates a request to advise a specific title.
     */
 // AdviserController@requestToAdvise
    public function requestToAdvise(Request $request, Title $title)
    {
        $user = $request->user(); // adviser

        if ($title->primary_adviser_id) {
            return back()->with('error', 'This title already has a primary adviser.');
        }
        if (! in_array($title->status, ['verified', 'awaiting_adviser'])) {
            return back()->with('error', 'This title is not open for advisers.');
        }

        $message = $request->string('message')->toString() ?: null;

        return DB::transaction(function () use ($title, $user, $message) {
            // lock single lifecycle row
            $req = \App\Models\AdviserRequest::where('title_id', $title->id)
                ->where('adviser_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($req && in_array($req->status, ['pending', 'accepted'])) {
                return back()->with('info', 'You already have a request for this title.');
            }

            if (! $req) {
                $req = new \App\Models\AdviserRequest([
                    'title_id'   => $title->id,
                    'adviser_id' => $user->id,
                ]);
            }

            // adviser-initiated → waits for student to accept
            $req->requested_by = 'adviser';
            $req->status       = 'pending';
            $req->message      = $message;
            $req->decided_at   = null;
            $req->save();

            // keep title searchable for advisers; optionally normalize to awaiting_adviser
            if ($title->status === 'verified') {
                $title->update(['status' => 'awaiting_adviser']);
            }

            // notify the student
            if (class_exists(\App\Models\Notification::class)) {
                \App\Models\Notification::create([
                    'user_id' => $title->owner_id,
                    'title'   => 'Adviser Request',
                    'message' => $user->name.' requested to advise your title "'.$title->title.'". Review and accept/decline.',
                    'is_read' => false,
                ]);
            }

            return back()->with('status', 'Request sent. The student will need to accept.');
        });
    }


    /**
     * Accept a pending request that was addressed to this adviser.
     * Locks this adviser as the primary adviser and closes other pending requests.
     */
   public function accept(Request $request, AdviserRequest $adviserRequest)
{
    $user = $request->user();

    if ($adviserRequest->adviser_id !== $user->id) {
        abort(403, 'Forbidden');
    }
    if ($adviserRequest->status !== 'pending') {
        return back()->with('error', 'This request is no longer pending.');
    }

    // Optional message to student on accept
    $data = $request->validate([
        'note' => 'nullable|string|max:2000',
    ]);

    DB::transaction(function () use ($adviserRequest, $user, $data) {
        $title = $adviserRequest->title()->lockForUpdate()->with('owner')->first();

        // If title already assigned, just mark this request as declined (race-safety)
        if ($title->primary_adviser_id) {
            $adviserRequest->update([
                'status'     => 'declined',
                'decided_at' => now(),
            ]);
            return;
        }

        // Accept this request
        $acceptNote = trim((string)($data['note'] ?? ''));

        // If you have a dedicated column for accept notes, store it there.
        // Otherwise reuse message with a prefix (like in decline).
        $newMessage = $adviserRequest->message
            ? rtrim($adviserRequest->message) . "\n[ACCEPT_NOTE] " . $acceptNote
            : ($acceptNote ? '[ACCEPT_NOTE] ' . $acceptNote : null);

        $adviserRequest->update([
            'status'     => 'accepted',
            'decided_at' => now(),
            'message'    => $newMessage,
        ]);

        // Assign adviser to title and move to admin gate
        $title->update([
            'primary_adviser_id'  => $user->id,
            'adviser_assigned_at' => now(),
            'status'              => 'awaiting_admin',
        ]);

        // Close other pending requests
        // Grab other pending advisers first (so we can notify them)
        $otherPending = AdviserRequest::where('title_id', $title->id)
            ->where('id', '!=', $adviserRequest->id)
            ->where('status', 'pending')
            ->get(['id','adviser_id']);

        // Close other pending requests
        AdviserRequest::whereIn('id', $otherPending->pluck('id'))
            ->update([
                'status'     => 'declined',
                'decided_at' => now(),
            ]);

        // Notify those advisers that the title was taken
        if (class_exists(\App\Models\Notification::class) && $otherPending->isNotEmpty()) {
            foreach ($otherPending as $op) {
                \App\Models\Notification::create([
                    'user_id' => $op->adviser_id,
                    'title'   => 'Title Taken',
                    'message' => 'The title “'.$title->title.'” has been assigned to another adviser.',
                    'is_read' => false,
                ]);
            }
        }


        // Notify the student (include note if present)
        if (class_exists(\App\Models\Notification::class)) {
            $msg = 'Your adviser accepted your request for the title “' . $title->title . '”.';
            if ($acceptNote) {
                $msg .= ' Message: ' . $acceptNote;
            }
            \App\Models\Notification::create([
                'user_id' => $title->owner_id,
                'title'   => 'Adviser Accepted',
                'message' => $msg,
                'is_read' => false,
            ]);
        }
    });

    return back()->with('status', 'Request accepted. Waiting for admin approval.');
}



    /**
     * Decline a pending request that was addressed to this adviser.
     */
    public function decline(Request $request, AdviserRequest $adviserRequest)
    {
        $user = $request->user();

        if ($adviserRequest->adviser_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if ($adviserRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        $data = $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        DB::transaction(function () use ($adviserRequest, $user, $data) {
            $title = $adviserRequest->title()->lockForUpdate()->with('owner')->first();

            // Store the reason. If you don't have a dedicated column, reuse "message".
            // Prefix to distinguish from initial request message.
            $reasonText = trim($data['reason']);
            $adviserRequest->update([
                'status'        => 'declined',
                'decided_at'    => now(),
                'message'       => '[DECLINE] ' . $reasonText, // change to ->decline_reason if you add a column
            ]);

            // Notify the student
            if (class_exists(\App\Models\Notification::class)) {
                \App\Models\Notification::create([
                    'user_id' => $title->owner_id,
                    'title'   => 'Adviser Declined Your Request',
                    'message' => $user->name . ' declined the title “' . $title->title . '”. Reason: ' . $reasonText,
                    'is_read' => false,
                ]);
            }
        });

        return back()->with('status', 'Request declined and student notified.');
    }








    public function listAdvisedTitles(Request $request)
    {
        $adviserId = $request->user()->id;

        $titles = Title::query()
            ->with(['owner'])                         // student owner
            ->where('primary_adviser_id', $adviserId)
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = trim($request->string('q'));
                $q->where(function ($w) use ($s) {
                    $w->where('title', 'like', "%{$s}%")
                      ->orWhereHas('owner', fn ($oq) => $oq->where('name', 'like', "%{$s}%"));
                });
            })
            ->orderByDesc('adviser_assigned_at')
            ->paginate(10)
            ->withQueryString();

        return view('adviser.advised_titles_index', compact('titles'));
    }

    /**
     * Show a single advised title, with student info and chapter list.
     */
    public function viewAdvisedTitle(Request $request, Title $title)
    {
        // Security: must be THIS adviser's title
        abort_if($title->primary_adviser_id !== $request->user()->id, 403);

        $title->load([
            'owner',                            // the student
            'documents' => fn ($q) => $q->orderBy('chapter'), // all docs; we'll display chapters
        ]);

        // Only "separate" docs are treated as chapters in UI
        $chapters = $title->documents->where('format', 'separate')->values();

        return view('adviser.advised_title_show', compact('title', 'chapters'));
    }
    public function showAdvisedChapter(Request $request, Title $title, Document $document)
    {
        // Security checks
        abort_if($title->primary_adviser_id !== $request->user()->id, 403);
        abort_if($document->title_id !== $title->id, 404);

        $document->load(['user', 'titleRelation']);

        // Load existing note (one per chapter per adviser based on the migration)
        $existingNote = AdviserNote::where('document_id', $document->id)
            ->where('adviser_id', $request->user()->id)
            ->first();

        return view('adviser.advised_chapter_show', [
            'title'        => $title,
            'document'     => $document,
            'existingNote' => $existingNote,   // ← pass it
        ]);
    }

    public function saveChapterNote(Request $request, Title $title, Document $document)
    {
        abort_if($title->primary_adviser_id !== $request->user()->id, 403);
        abort_if($document->title_id !== $title->id, 404);

        $data = $request->validate([
            'message' => 'required|string',
        ]);

        $adviser = $request->user();

        // One note per (adviser x document) — create or update
        $note = AdviserNote::firstOrNew(
            [
                'document_id' => $document->id,
                'adviser_id'  => $adviser->id,
            ],
            [
                'title_id'    => $title->id,
                'student_id'  => $document->user_id,
            ]
        );

        $note->content = $data['message'];
        $note->save();


      \App\Models\Notification::create([
        'user_id' => $document->user_id,   // student who owns the document
        'title'   => $adviser->name . ' added a note',
        'message' =>   $document->chapter. 
                     ' | ' .  $title->title,
        'is_read' => false,
        ]);

        return back()->with('success', $note->wasRecentlyCreated ? 'Note created.' : 'Note updated.');
    }
}
