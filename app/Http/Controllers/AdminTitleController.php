<?php

namespace App\Http\Controllers;

use App\Models\Title;
use App\Models\Document;
use Illuminate\Http\Request;

class AdminTitleController extends Controller
{
    /**
     * Show all titles that have been submitted (no approval flow).
     */
    public function submittedTitles(Request $request)
    {
        $q = Title::with(['user', 'finalDocument'])
            ->where('status', 'submitted')         // submitted by the student
            ->orderByDesc('submitted_at');

        // 🔍 Search by title or student name
        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $q->where(function ($qq) use ($search) {
                $qq->where('title', 'like', "%{$search}%")
                   ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // 📄 Per-page (default 10 -> set to 5 here to match your previous screens)
        $perPage = (int) $request->input('per_page', 5);

        $titles = $q->paginate($perPage)->withQueryString();

        return view('admin.titles.submitted', compact('titles'));
    }

    /**
     * View a submitted (final) document.
     * You can link this from the list using the document id.
     */
    public function viewSubmittedDocument(Document $document)
    {
        $document->load(['user', 'titleRelation']);
        return view('documents.final_document_viewer', compact('document'));
    }
}
