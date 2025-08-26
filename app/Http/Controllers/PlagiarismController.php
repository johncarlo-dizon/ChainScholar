<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\PlagiarismService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PlagiarismController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private PlagiarismService $plag) {}

    public function checkPlagiarismLive(Request $request)
    {
        $document = Document::findOrFail((int)$request->input('document_id'));
        $this->authorize('view', $document);

        $raw = $request->input('content_html') ?: $request->input('content', '');
        $score = $this->plag->quickScore($raw, $document);

        return response()->json(['score' => $score]);
    }

    public function checkPlagiarismDetailed(Request $request)
    {
        $document = Document::findOrFail((int)$request->input('document_id'));
        $this->authorize('view', $document);

        $html = (string)$request->input('content_html', '');
        $min  = (int)$request->input('min_percent', 0);

        return response()->json($this->plag->detailedMatches($html, $document, $min));
    }
}
