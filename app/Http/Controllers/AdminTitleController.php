<?php

namespace App\Http\Controllers;

use App\Models\Title;
use App\Models\Document;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AdviserRequest;

class AdminTitleController extends Controller
{
    /**
     * Awaiting admin approval list.
     */
       public function return(Request $request, Title $title)
        {
    

        $data = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $reason = trim((string)($data['reason'] ?? ''));
        $student = $title->owner;                 // Title owner (student)
        $prevAdviserId = $title->primary_adviser_id;
        $prevAdviser   = $title->adviser;         // assuming relation ->adviser

        DB::transaction(function () use ($title, $reason, $student, $prevAdviserId, $prevAdviser) {
            // 1) Close all adviser requests for this title (so the student starts fresh)
            AdviserRequest::where('title_id', $title->id)
                ->whereIn('status', ['pending','accepted'])
                ->update([
                    'status'     => 'declined',
                    'decided_at' => now(),
                ]);

            // 2) Revert title back to "awaiting_adviser" and clear assignment
            $title->update([
                'status'              => 'awaiting_adviser',
                'primary_adviser_id'  => null,
                'adviser_assigned_at' => null,
            ]);

            // 3) Notify student
            $studentMsg = 'Your title "'.$title->title.'" was returned. '
                .'Please choose an adviser again.'
                .($reason !== '' ? ' Reason: '.$reason : '');

            Notification::create([
                'user_id' => $student->id,
                'title'   => 'Title Returned',
                'message' => $studentMsg,
                'is_read' => false,
            ]);

            // 4) Notify previous adviser (if there was one)
            if ($prevAdviserId && $prevAdviser) {
                $adviserMsg = 'The title "'.$title->title.'" was returned.'
                    .($reason !== '' ? ' Reason: '.$reason : '');

                Notification::create([
                    'user_id' => $prevAdviser->id,
                    'title'   => 'Title Returned',
                    'message' => $adviserMsg,
                    'is_read' => false,
                ]);
            }
        });

        return back()->with('success', 'Title returned to student. They must choose an adviser again.');
    }
    public function awaiting(Request $request)
    {
        $q = Title::with([
                'owner',
                'finalDocument',
                // NEW: load adviser and full profile data
                'adviser',
                'adviser.adviserProfile.achievements',
                'adviser.adviserProfile.researchInterests',
            ])
            ->where('status', 'awaiting_admin')
            ->orderByDesc('adviser_assigned_at');

        if ($request->filled('search')) {
            $s = trim($request->string('search'));
            $q->where(function ($qq) use ($s) {
                $qq->where('title', 'like', "%{$s}%")
                   ->orWhereHas('owner', fn($u) => $u->where('name', 'like', "%{$s}%"));
            });
        }

        $titles = $q->paginate((int)$request->input('per_page', 10))->withQueryString();

        return view('admin.titles.awaiting_admin', compact('titles'));
    }

    /**
     * APPROVE adviser assignment → unlock editing.
     */
    public function approve(Request $request, Title $title)
    {
        abort_if($title->status !== 'awaiting_admin', 400, 'Not awaiting admin.');

        DB::transaction(function () use ($title) {
            $title->update([
                'status'      => 'in_advising', // ← from now on, editable
                'approved_at' => now(),
            ]);

            if (class_exists(\App\Models\Notification::class)) {
                \App\Models\Notification::create([
                    'user_id' => $title->owner_id,
                    'title'   => 'Admin Approved',
                    'message' => 'Your adviser was approved by admin. You can now edit your chapters.',
                    'is_read' => false,
                ]);
            }
        });

        return back()->with('success', 'Title moved to advising (editing unlocked).');
    }

    /**
     * RETURN for correction / change adviser
     */
    

    /**
     * Your existing list of submitted titles (finals).
     */
    public function submittedTitles(Request $request)
    {
        $q = Title::with(['user', 'finalDocument'])
            ->where('status', 'submitted')
            ->orderByDesc('submitted_at');

        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $q->where(function ($qq) use ($search) {
                $qq->where('title', 'like', "%{$search}%")
                   ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $perPage = (int) $request->input('per_page', 5);
        $titles = $q->paginate($perPage)->withQueryString();

        return view('admin.titles.submitted', compact('titles'));
    }

    /**
     * View a submitted (final) document.
     */
    public function viewSubmittedDocument(Document $document)
    {
        $document->load(['user', 'titleRelation']);
        return view('documents.final_document_viewer', compact('document'));
    }
}
