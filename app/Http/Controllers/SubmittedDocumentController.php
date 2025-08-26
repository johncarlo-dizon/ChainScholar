<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubmittedDocument;
use Illuminate\Support\Facades\Auth;

class SubmittedDocumentController extends Controller
{
    // Show all documents submitted by the logged-in user

    public function index()
    {
        $submittedDocuments = SubmittedDocument::where('user_id', Auth::id())
                                ->orderBy('submitted', 'desc')
                                ->get();

        return view('documents.submitted_documents', compact('submittedDocuments'));
    }

    // Show detailed view of a single submitted document
    public function show($id)
    {
        $document = SubmittedDocument::where('id', $id)
                        ->where('user_id', Auth::id()) // Ensure ownership
                        ->firstOrFail();

        return view('submitted_documents.show', compact('document'));
    }
}
