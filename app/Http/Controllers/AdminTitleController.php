<?php

namespace App\Http\Controllers;

use App\Models\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class AdminTitleController extends Controller
{
    public function pendingTitles()
    {
        $titles = Title::with('user')
            ->where('status', 'pending')
            ->orderByDesc('submitted_at')
            ->get();

        return view('admin.titles.pending', compact('titles'));
    }

    public function review($id)
    {
        $document = \App\Models\Document::with('user', 'titleRelation')->findOrFail($id);
        return view('admin.titles.admin_view_pending_document', compact('document'));
    }

    public function approvedTitles()
    {
        $titles = Title::with('user')
            ->where('status', 'approved')
            ->orderByDesc('approved_at')
            ->get();

        return view('admin.titles.approved', compact('titles'));
    }

    public function approve($id)
    {
        $title = Title::findOrFail($id);
        $title->status = 'approved';
        $title->approved_at = now();
        $title->review_comments = null;
        $title->save();

        // ✅ Notify the user
        Notification::create([
            'user_id' => $title->user_id,
            'title' => 'Title Approved',
            'message' => 'Document "' . $title->title . '" has been approved.',
            'is_read' => false,
        ]);

        return redirect()->route('admin.titles.pending')->with('success', 'Document approved successfully.');
    }

    public function return(Request $request)
    {
        $request->validate([
            'title_id' => 'required|exists:titles,id',
            'review_comments' => 'required|string'
        ]);

        $title = Title::findOrFail($request->title_id);
        $title->status = 'returned';
        $title->review_comments = $request->review_comments;
        $title->returned_at = now();
        $title->save();

        // ✅ Notify the user
        Notification::create([
            'user_id' => $title->user_id,
            'title' => 'Title Returned',
            'message' => 'Document "' . $title->title . '" was returned with comments: "' . $title->review_comments . '"',
            'is_read' => false,
        ]);

        return redirect()->route('admin.titles.pending')->with('success', 'Document returned with comments.');
    }

    public function viewFinal($id)
    {
        $document = \App\Models\Document::with('user', 'titleRelation')->findOrFail($id);
        return view('documents.final_document_viewer', compact('document'));
    }
}
