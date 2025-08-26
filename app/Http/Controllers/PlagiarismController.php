<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\PlagiarismService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class PlagiarismController extends Controller
{
    public function __construct(private PlagiarismService $plag) {}
    use AuthorizesRequests;  

    // POST /documents/check-plagiarism
    public function checkPlagiarismLive(Request $request)
    {
        $document = Document::findOrFail((int) $request->input('document_id'));
        $this->authorize('view', $document); // ← add this
        $raw = $request->input('content_html') ?: $request->input('content', '');
        $scorePct = $this->plag->quickScore($raw, $document);

        return response()->json(['score' => $scorePct]);
    }

    // POST /documents/check-plagiarism-detailed
    public function checkPlagiarismDetailed(Request $request)
    {
        $document = Document::findOrFail((int) $request->input('document_id'));
        $this->authorize('view', $document); // ← add this
        $html = $request->input('content_html', '');
        $min  = (int) $request->input('min_percent', 0);
        $data = $this->plag->detailedMatches($html, $document, $min);

        return response()->json($data);
    }
}
