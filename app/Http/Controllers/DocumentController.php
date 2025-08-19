<?php

namespace App\Http\Controllers;
use App\Models\Notification;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Title;
use Illuminate\Support\Str;
use App\Models\AdviserNote;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    use AuthorizesRequests;


// ===================== PLAG START =====================

/** Windowing & scoring knobs */
private const PLAG_WINDOW_WORDS       = 50;   // sliding window size
private const PLAG_STRIDE_WORDS       = 25;   // step size between windows
private const PLAG_MIN_CHUNK_WORDS    = 12;   // ignore tiny chunks
private const PLAG_MATCH_THRESHOLD    = 0.60; // 60% cosine → "match"
private const PLAG_TOP_MATCHES        = 30;   // cap returned matches
private const PLAG_OVERALL_MULTIPLIER = 100;  // to percent

/**
 * Quick score (used by sidebar). Uses same windowed metric as offcanvas.
 */
public function checkPlagiarismLive(Request $request)
{
    $document = Document::findOrFail((int) $request->input('document_id'));
    $raw      = $request->input('content_html') ?: $request->input('content', '');
    $scorePct = $this->plagMaxChunkSimilarity($raw, $document);

    return response()->json(['score' => $scorePct]);
}

/**
 * Detailed matches for the offcanvas.
 */
public function checkPlagiarismDetailed(Request $request)
{
    $document   = Document::findOrFail((int) $request->input('document_id'));
    $html       = $request->input('content_html', '');
    $txt        = $this->plagStartAfterChapterOne($this->plagCleanText($html));
    $yourChunks = $this->plagMakeChunks($txt);

    // Reuse the same cached candidate chunks as the quick score
    $candidateChunks = $this->plagBuildCandidateChunks($document);

    $matches    = [];
    $overallMax = 0.0;

    foreach ($yourChunks as $yc) {
        $yVec = $yc['vec']; $yMag = $yc['mag'];
        if ($yMag == 0) continue;

        foreach ($candidateChunks as $cc) {
            $den = $yMag * $cc['mag']; if ($den == 0) continue;
            $sim = $this->plagDotProduct($yVec, $cc['vec']) / $den;

            if ($sim > $overallMax) $overallMax = $sim;

            if ($sim >= self::PLAG_MATCH_THRESHOLD) {
                $matches[] = [
                    'percent'        => round($sim * self::PLAG_OVERALL_MULTIPLIER, 2),
                    'your_excerpt'   => $yc['text'],
                    'source_excerpt' => $cc['text'],
                    'source_title'   => $cc['source_title'],
                    'source_chapter' => $cc['source_chapter'],
                    'document_id'    => $cc['document_id'],
                ];
            }
        }
    }

    usort($matches, fn($a,$b)=>$b['percent'] <=> $a['percent']);
    $matches = array_slice($matches, 0, self::PLAG_TOP_MATCHES);

    return response()->json([
        'score'   => round($overallMax * self::PLAG_OVERALL_MULTIPLIER, 2),
        'matches' => $matches,
        'meta'    => [
            'threshold'  => self::PLAG_MATCH_THRESHOLD * 100,
            'window'     => self::PLAG_WINDOW_WORDS,
            'stride'     => self::PLAG_STRIDE_WORDS,
            'candidates' => count($candidateChunks),
        ],
    ]);
}

/** Build sliding-window chunks */
protected function plagMakeChunks(string $text): array
{
    $words = preg_split('/\s+/u', trim($text));
    $words = array_values(array_filter($words, fn($w)=>$w!==''));

    $chunks = []; $n = count($words);
    if ($n < self::PLAG_MIN_CHUNK_WORDS) return [];

    for ($i=0; $i<$n; $i+=self::PLAG_STRIDE_WORDS) {
        $slice = array_slice($words, $i, self::PLAG_WINDOW_WORDS);
        if (count($slice) < self::PLAG_MIN_CHUNK_WORDS) break;

        $chunkText = implode(' ', $slice);
        $vec = $this->plagTermFreqMap($this->plagTokenize($this->plagNormalizeText($chunkText)));
        $mag = $this->plagMagnitude($vec);

        $chunks[] = ['text'=>$chunkText, 'vec'=>$vec, 'mag'=>$mag];
    }
    return $chunks;
}

/** Start comparisons only AFTER "Chapter 1/Chapter I/Chapter One"; fallback early "Introduction" */
protected function plagStartAfterChapterOne(string $text): string
{
    $t = ltrim($text);

    // "Chapter 1 / I / One" (case-insensitive, unicode)
    if (preg_match('/\bchapter\s*(?:1|i|one)\b/iu', $t, $m, PREG_OFFSET_CAPTURE)) {
        $pos = $m[0][1];
        return ltrim(mb_substr($t, $pos));
    }

    // Early "Introduction" within first 25% of content
    if (preg_match('/\bintroduction\b/iu', $t, $m2, PREG_OFFSET_CAPTURE)) {
        $pos = $m2[0][1];
        if ($pos < (int)(mb_strlen($t) * 0.25)) return ltrim(mb_substr($t, $pos));
    }

    return $t;
}

/** Clean HTML → text */
protected function plagCleanText($html)
{
    $html = preg_replace('/<img[^>]+src="data:image\/[^"]+"[^>]*>/i', '', $html);              // drop base64 imgs
    $html = preg_replace('/<div class="ck[^"]*"[^>]*>.*?<\/div>/si', '', $html);               // drop ck widgets
    $text = strip_tags($html);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

/** Normalize → tokenize → TF */
protected function plagNormalizeText($text)
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);

    $stop = ['the','is','at','which','on','a','an','of','to','in','and','with','for','as','by','are'];
    $words = explode(' ', $text);
    $filtered = array_filter($words, fn($w)=>$w!=='' && !in_array($w, $stop, true));

    return implode(' ', $filtered);
}
protected function plagTokenize($text){ return array_filter(explode(' ', $text)); }
protected function plagTermFreqMap($tokens){
    $f=[]; foreach($tokens as $t){ $t=trim($t); if($t==='')continue; $f[$t]=($f[$t]??0)+1; } return $f;
}
protected function plagMagnitude($vector){ $s=0.0; foreach($vector as $v){ $s+=$v*$v; } return sqrt($s); }
protected function plagDotProduct($a,$b){ $d=0.0; foreach($a as $k=>$v){ if(isset($b[$k])) $d+=$v*$b[$k]; } return $d; }

/**
 * Build & cache candidate chunks from all other documents (exclude same title_id).
 * Cache key is derived from the current doc's title_id so both endpoints hit the same set.
 * Requires: Document model has `title()` relation.
 */
private function plagBuildCandidateChunks(Document $document): array
{
    $cacheKey = 'plag:cchunks:exclude_title:' . $document->title_id;

    return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($document) {
        $cands = Document::where('title_id', '!=', $document->title_id)
            ->with('title:id,title')
            ->get(['id','title_id','chapter','content']);

        $out = [];
        foreach ($cands as $d) {
            $srcText = $this->plagStartAfterChapterOne($this->plagCleanText($d->content));
            foreach ($this->plagMakeChunks($srcText) as $chunk) {
                $out[] = [
                    'document_id'    => $d->id,
                    'source_title'   => optional($d->title)->title ?? 'Untitled',
                    'source_chapter' => $d->chapter ?? 'Unknown Chapter',
                    'text'           => $chunk['text'],
                    'vec'            => $chunk['vec'],
                    'mag'            => $chunk['mag'],
                ];
            }
        }
        return $out;
    });
}

/** Max local similarity between your chunks and candidate chunks (percent) */
private function plagMaxChunkSimilarity(string $rawHtmlOrText, Document $document): float
{
    $text = $this->plagStartAfterChapterOne($this->plagCleanText($rawHtmlOrText));
    $yourChunks = $this->plagMakeChunks($text);
    if (empty($yourChunks)) return 0.0;

    $cChunks = $this->plagBuildCandidateChunks($document);
    if (empty($cChunks)) return 0.0;

    $overallMax = 0.0;
    foreach ($yourChunks as $yc) {
        $yVec = $yc['vec']; $yMag = $yc['mag']; if ($yMag==0) continue;
        foreach ($cChunks as $cc) {
            $den = $yMag * $cc['mag']; if ($den==0) continue;
            $sim = $this->plagDotProduct($yVec, $cc['vec']) / $den;
            if ($sim > $overallMax) $overallMax = $sim;
        }
    }
    return round($overallMax * 100, 2);
}

/** Compatibility wrapper */
private function computePlagiarismScoreForContent(string $rawHtmlOrText, Document $document): float
{
    return $this->plagMaxChunkSimilarity($rawHtmlOrText, $document);
}

// ===================== PLAG END =====================





    public function showSearchDashboard()
    {
        return view('documents.dashboard'); // Initial search screen only
    }

    public function searchResearch(Request $request)
    {
        $query = $request->input('query');

        $approvedTitles = Title::with('user')
            ->where('status', 'submitted')
            ->where('owner_id', '!=', auth()->id())
            ->get();

        $results = [];

        foreach ($approvedTitles as $title) {
            $similarity = $this->cosSimilarity($query, $title->title);
            if ($similarity >= 0.3) { // You can adjust this threshold
                $results[] = [
                    'title' => $title->title,
                    'id' => $title->id,
                    'similarity' => $similarity
                ];
            }
        }

        // Sort by highest similarity
        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return view('documents.dashboard', compact('results', 'query'));
    }

    public function viewResearch($id)
    {
        $title = Title::with(['user', 'finalDocument'])->findOrFail($id);
        return view('documents.search_research', compact('title'));
    }

    // ✅ Cosine Similarity Helper
    private function cosSimilarity($str1, $str2)
    {
        $tokens1 = array_count_values(str_word_count(strtolower($str1), 1));
        $tokens2 = array_count_values(str_word_count(strtolower($str2), 1));

        $allWords = array_unique(array_merge(array_keys($tokens1), array_keys($tokens2)));

        $vec1 = $vec2 = [];

        foreach ($allWords as $word) {
            $vec1[] = $tokens1[$word] ?? 0;
            $vec2[] = $tokens2[$word] ?? 0;
        }

        $dotProduct = array_sum(array_map(fn($a, $b) => $a * $b, $vec1, $vec2));
        $magnitude1 = sqrt(array_sum(array_map(fn($a) => $a * $a, $vec1)));
        $magnitude2 = sqrt(array_sum(array_map(fn($b) => $b * $b, $vec2)));

        if ($magnitude1 * $magnitude2 == 0) return 0;

        return $dotProduct / ($magnitude1 * $magnitude2);
    }










    // GOOGLE SCHOLAR SEARCH LIKE END ----------------------------------------------------------------



    public function index()
    {
        $user = auth()->user();
    
        $documents = $user->documents()->latest()->get();
        $titles = $user->titles()->latest()->get(); // ✅ Add this line
    
        return view('documents.index', compact('documents', 'titles')); // ✅ Pass both
    }

    public function showSubmittedDocuments(Request $request)
    {
        $query = auth()->user()
            ->titles()
            ->whereIn('status', ['pending', 'approved', 'returned'])
            ->latest();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 👇 Paginate (change 10 to whatever you want)
        $titles = $query->paginate(7)->withQueryString();

        return view('documents.submitted_documents', compact('titles'));
    }

    
    public function viewFinalDocument($id)
    {
        $document = \App\Models\Document::with('user', 'titleRelation')->findOrFail($id);

        return view('documents.final_document_viewer', compact('document'));
    }

    public function cancelSubmission($id)
    {
        $title = \App\Models\Title::findOrFail($id);

        // Reset title status and submitted_at
        $title->update([
            'status' => 'draft',
            'submitted_at' => null,
        ]);

        return redirect()->back()->with('success', 'Submission has been cancelled.');
    }


    

   public function submitFinal(Request $request, $title_id)
{
    $request->validate([
        'finaldocument_id' => 'required|exists:documents,id',
        'authors'          => 'required|string',
        'abstract'         => 'required|string',
        'research_type'    => 'required|string',
        'final_content'    => 'nullable|string',
    ]);

    $title = Title::findOrFail($title_id);
    $document = Document::findOrFail($request->finaldocument_id);

    // 🔒 Ownership & consistency checks
    if ((int) $title->owner_id !== (int) auth()->id()) {
        abort(403, 'You can only submit your own title.');
    }
    if ((int) $document->user_id !== (int) auth()->id()) {
        abort(403, 'You can only submit your own document.');
    }
    if ((int) $document->title_id !== (int) $title->id) {
        return back()->with('error', 'Selected document does not belong to this title.');
    }

    $finalHtml = $request->final_content ?? $document->content;
    $plagPct   = $this->computePlagiarismScoreForContent($finalHtml, $document);

    DB::transaction(function () use ($document, $title, $finalHtml, $plagPct, $request) {
        // ✅ Update the chosen chapter/content & similarity
        $document->update([
            'content'           => $finalHtml,
            'plagiarism_score'  => $plagPct,
        ]);

        // ✅ Mark the Title as submitted (no admin approval stage)
        $title->update([
            'final_document_id' => $document->id,   // ← note the new snake_case column
            'authors'           => $request->authors,
            'abstract'          => $request->abstract,
            'research_type'     => $request->research_type,
            'status'            => 'submitted',     // ← use your new status
            'submitted_at'      => now(),
            // Optional: clear any legacy review fields
            'review_comments'   => null,
            'approved_at'       => null,
            'returned_at'       => null,
        ]);
    });

    return redirect()
        ->route('titles.index')
        ->with('success', "Final document submitted! Similarity: {$plagPct}%");
}




    
    
    
    
    


    public function destroy($id)
    {
        $doc = Document::findOrFail($id);
        $doc->delete();

        return redirect()->back()->with('success', 'Chapter deleted successfully.');
    }



 

    public function create(Request $request)
    {
        $title = $request->input('title'); // from verification step
        return view('documents.editor', compact('title'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'title_id' => 'required|exists:titles,id',
            'chapter' => 'required|string|max:255',
        ]);

        $document = Document::create([
            'user_id' => auth()->id(),
            'title_id' => $request->title_id,
            'chapter' => $request->chapter,
            'content' => '', // Start empty
        ]);

        // Redirect to editor
        return redirect()->route('documents.edit', $document->id);
    }

    public function edit(Document $document)
    {
        $this->authorize('update', $document);

        // Prefer the primary adviser's note; otherwise show the latest note for this chapter
        $title = $document->titleRelation()->select('id', 'primary_adviser_id')->first();
        $adviserNote = null;

        if ($title && $title->primary_adviser_id) {
            $adviserNote = AdviserNote::with('adviser')
                ->where('document_id', $document->id)
                ->where('adviser_id', $title->primary_adviser_id)
                ->first();
        } else {
            $adviserNote = AdviserNote::with('adviser')
                ->where('document_id', $document->id)
                ->latest('updated_at')
                ->first();
        }

        return view('documents.editor', compact('document', 'adviserNote'));
    }



   






    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $request->validate([
            'content' => 'required'
        ]);

        // Compute plagiarism score but don't block
        $plagPct = $this->computePlagiarismScoreForContent($request->content, $document);

        $document->update([
            'content' => $request->content,
            'plagiarism_score' => $plagPct,
        ]);

        return redirect()
            ->route('titles.chapters', $document->title_id)
            ->with('success', "Chapter updated successfully! Similarity: {$plagPct}%");
    }



    public function show(Document $document)
    {
        $this->authorize('view', $document);
        return view('documents.viewer', compact('document'));
    }

    // Image upload handler for CKEditor
    public function uploadImage(Request $request)
    {
        $request->validate(['upload' => 'required|image|max:2048']);
        
        $path = $request->file('upload')->store('public/editor-images');
        $url = Storage::url($path);

        return response()->json([
            'url' => $url
        ]);
    }








 





    //DocumentController.php
    // TITLE VERIFY -----------------------------------------------------------------------------------------



    
public function suggestTitlesGemini(Request $request)
{
    $request->validate([
        'draft_title'     => 'required|string|min:5',
        'existing_titles' => 'array',
        'context'         => 'array',
    ]);

    $draft    = trim($request->input('draft_title'));
    $existing = $request->input('existing_titles', []);
    $context  = $request->input('context', []);

    $apiKey   = config('services.gemini.key');
    $model    = config('services.gemini.model', 'gemini-1.5-pro');
    $endpoint = rtrim(config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta'), '/');

    if (!$apiKey) {
        return response()->json(['error' => 'Gemini API key missing'], 500);
    }

    // Cache per rejected draft
    $cacheKey = 'gemini_title_enhance_v2:' . md5($draft . json_encode(array_slice($existing, 0, 50)) . json_encode($context));
    if (Cache::has($cacheKey)) {
        return response()->json(Cache::get($cacheKey));
    }

    // === Prompt as "Academic Research Title Enhancement Assistant" ===
    $rules = implode("\n", [
        "Act as an Academic Research Title Enhancement Assistant.",
        "Input is a REJECTED academic title. Task: rewrite to produce improved titles that:",
        "- Stay relevant and connected to the original topic.",
        "- Address a clear, significant, and specific problem in the field.",
        "- Integrate latest trends/innovations/emerging concepts relevant to the field.",
        "- Are concise, professional, and suitable for academic approval.",
        "- Sound innovative, impactful, and aligned with modern developments.",
        "Return STRICT JSON with EXACTLY this schema:",
        "{ \"suggestions\": [",
        "  {\"title\":\"...\",\"why\":\"(1–2 sentences explaining the improvement)\"},",
        "  {\"title\":\"...\",\"why\":\"...\"},",
        "  {\"title\":\"...\",\"why\":\"...\"}",
        "] }",
        "Constraints:",
        "- Exactly 3 suggestions.",
        "- Each title ≤ 16 words; avoid colon stacking and buzzword soup.",
        "- Keep close to the original scope (do not change the topic entirely).",
    ]);

    $payloadBlock = [
        "draft_title"  => $draft,
        "context"      => $context,
        "avoid_titles" => array_values($existing),
    ];

    try {
        $res = Http::timeout(20)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$endpoint}/models/{$model}:generateContent?key={$apiKey}", [
                "generationConfig" => [
                    "temperature" => 0.5,
                    "topK" => 40,
                    "topP" => 0.9,
                    "maxOutputTokens" => 512,
                    "response_mime_type" => "application/json",
                ],
                "contents" => [
                    [
                        "role" => "user",
                        "parts" => [[ "text" => $rules ]]
                    ],
                    [
                        "role" => "user",
                        "parts" => [[ "text" => json_encode($payloadBlock, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ]]
                    ],
                ],
            ]);

        if (!$res->ok()) {
            return response()->json(['error' => 'Gemini request failed', 'meta' => $res->json()], 502);
        }

        $raw = $res->json();

        // ---- Robust JSON extraction ----
        $jsonStr = data_get($raw, 'candidates.0.content.parts.0.text');
        if (!$jsonStr) {
            // Try inlineData
            $parts = data_get($raw, 'candidates.0.content.parts', []);
            foreach ($parts as $p) {
                if (isset($p['inlineData']['mimeType']) && str_contains($p['inlineData']['mimeType'], 'json')) {
                    $jsonStr = base64_decode($p['inlineData']['data'] ?? '');
                    break;
                }
            }
        }
        if (!$jsonStr) {
            // If blocked/safety etc.
            return response()->json([
                'error' => 'Gemini returned no candidates (possibly safety blocked).',
                'feedback' => data_get($raw, 'promptFeedback', []),
                'raw' => $raw,
            ], 500);
        }

        // Parse and sanitize
        $parsed = json_decode($jsonStr, true);
        if (!is_array($parsed) || !isset($parsed['suggestions']) || !is_array($parsed['suggestions'])) {
            // Attempt to salvage JSON substring
            if (preg_match('/\{.*\}/s', $jsonStr, $m)) {
                $parsed = json_decode($m[0], true);
            }
        }
        if (!is_array($parsed) || !isset($parsed['suggestions']) || !is_array($parsed['suggestions'])) {
            $parsed = ['suggestions' => []];
        }

        // Normalize; keep exactly 3 items
        $rawSug = collect($parsed['suggestions'])
            ->map(function ($s) {
                return [
                    'title' => trim((string)($s['title'] ?? '')),
                    'why'   => trim((string)($s['why'] ?? '')),
                ];
            })
            ->filter(fn ($s) => $s['title'] !== '')
            ->take(6) // temp buffer; we will re-rank and then take 3
            ->values();

        // Score each suggestion with internal & web similarity
        $scored = $this->scoreSuggestions($rawSug->all(), $existing);

        // Keep only those <30 on both, otherwise fallback to lowest combined
        $approved = array_values(array_filter($scored, fn($x) => $x['internal_pct'] < 30 && $x['web_pct'] < 30));
        $final = $approved;

        if (count($final) < 3 && count($scored)) {
            usort($scored, fn($a,$b)=> ($a['internal_pct'] + $a['web_pct']) <=> ($b['internal_pct'] + $b['web_pct']));
            foreach ($scored as $cand) {
                if (count($final) >= 3) break;
                if (!in_array($cand, $final, true)) $final[] = $cand;
            }
        }

        // Ensure exactly 3
        $final = array_slice($final, 0, 3);

        $out = ['suggestions' => $final];
        Cache::put($cacheKey, $out, now()->addMinutes(30));
        return response()->json($out);

    } catch (\Throwable $e) {
        return response()->json(['error' => 'Gemini error', 'message' => $e->getMessage()], 500);
    }
}

/**
 * Score suggestions with internal cosine similarity and web similarity (Semantic Scholar).
 * Returns: [{ title, why, internal_pct, web_pct, approved }]
 */
private function scoreSuggestions(array $suggestions, array $existingTitles): array
{
    // Prepare internal token maps once
    $existing = array_values(array_filter(array_map('strval', $existingTitles)));
    $scored = [];

    foreach ($suggestions as $s) {
        $t = $s['title'];

        // Internal max cosine
        $internalMax = 0.0;
        foreach ($existing as $ex) {
            $c = self::cosineSimilarity($t, $ex);
            if ($c > $internalMax) $internalMax = $c;
        }
        $internalPct = round($internalMax * 100, 2);

        // Web max (call Semantic Scholar once per suggestion)
        $webPct = $this->computeWebSimilarityPercent($t);

        $scored[] = [
            'title'        => $t,
            'why'          => $s['why'],
            'internal_pct' => $internalPct,
            'web_pct'      => $webPct,
            'approved'     => ($internalPct < 30 && $webPct < 30),
        ];
    }

    return $scored;
}

/**
 * Compute web similarity percent (0–100) using Semantic Scholar (same approach as checkWebTitleSimilarity).
 */
private function computeWebSimilarityPercent(string $title): float
{
    try {
        $response = Http::retry(2, 200)
            ->get('https://api.semanticscholar.org/graph/v1/paper/search', [
                'query'  => $title,
                'fields' => 'title',
                'limit'  => 5,
            ]);

        if (!$response->ok()) return 0.0;
        $items = $response->json('data', []);
        $max = 0.0;
        foreach ($items as $it) {
            $webTitle = (string)($it['title'] ?? '');
            $score = self::cosineSimilarity(strtolower($title), strtolower($webTitle));
            $pct = round($score * 100, 2);
            if ($pct > $max) $max = $pct;
        }
        return $max;
    } catch (\Throwable $e) {
        return 0.0;
    }
}


public function checkTitleSimilarity(Request $request)
{
    $inputTitle = $request->input('title');
    $documentId = $request->input('document_id'); 

    $documents = Document::all();
    $similarities = [];

    foreach ($documents as $doc) {
        if ($documentId && $doc->id == $documentId) continue;

        similar_text(strtolower($inputTitle), strtolower($doc->title), $percent);
        $similarities[] = [
            'existing_title' => $doc->title,
            'similarity' => round($percent, 2)
        ];
    }

    // Sort from highest to lowest similarity
    usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
    $maxSimilarity = $similarities[0]['similarity'] ?? 0;

    return response()->json([
        'max_similarity' => $maxSimilarity,
        'similarities' => $similarities,
        'approved' => $maxSimilarity < 30
    ]);
}



    
public function checkWebTitleSimilarity(Request $request)
{
    $inputTitle = strtolower($request->input('title'));
    $attempt = (int) $request->input('attempt', 1);

    $cacheKey = 'web_similarity_' . md5($inputTitle);

    // Serve from cache only on first attempt (so a bad first hit won't keep poisoning)
    if ($attempt === 1 && Cache::has($cacheKey)) {
        return response()->json(Cache::get($cacheKey));
    }

    // Fresh call each attempt>1 (or first attempt without cache)
    $response = Http::retry(2, 200) // quick server-side retry for transient errors
        ->get('https://api.semanticscholar.org/graph/v1/paper/search', [
            'query' => $inputTitle,
            'fields' => 'title',
            'limit'  => 2,
        ]);

    $items = $response->ok() ? $response->json('data', []) : [];
    $similarities = [];

    foreach ($items as $item) {
        $webTitle = strtolower($item['title'] ?? '');
        $score = self::cosineSimilarity($inputTitle, $webTitle);
        $similarities[] = [
            'title' => $item['title'] ?? '',
            'similarity' => round($score * 100, 2),
        ];
    }

    usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
    $max = $similarities[0]['similarity'] ?? 0;

    $data = [
        'max_similarity' => $max,
        'approved'       => $max < 30,
        'results'        => $similarities,
    ];

    // 💡 Cache ONLY if we actually have results.
    if (!empty($similarities)) {
        Cache::put($cacheKey, $data, now()->addMinutes(60));
    }

    return response()->json($data);
}


// Helper functions
private static function cosineSimilarity($textA, $textB)
{
    $tokensA = self::tokenize($textA);
    $tokensB = self::tokenize($textB);

    $freqA = self::termFreqMap($tokensA);
    $freqB = self::termFreqMap($tokensB);

    $dotProduct = self::dotProduct($freqA, $freqB);
    $magnitude = self::magnitude($freqA) * self::magnitude($freqB);

    return $magnitude == 0 ? 0 : $dotProduct / $magnitude;
}

private static function tokenize($text)
{
    $stopWords = ['a','an','and','are','as','at','be','by','for','from','has','he','in','is','it','its','of','on','that','the','to','was','were','will','with','study','research','paper','report','project','capstone','case','review','investigation','analysis','approach','effect','impact','model','method','methods','design','development','evaluation','implementation','system','application','framework','prototype','solution','tool','tools','technology','technologies','process','exploration','assessment'];
    $words = preg_split('/\W+/', strtolower($text));
    return array_values(array_filter($words, fn($word) => !in_array($word, $stopWords) && strlen($word) > 1));
}

private static function termFreqMap($tokens)
{
    $freq = [];
    foreach ($tokens as $token) {
        $freq[$token] = ($freq[$token] ?? 0) + 1;
    }
    return $freq;
}

private static function dotProduct($mapA, $mapB)
{
    $dot = 0;
    foreach ($mapA as $key => $val) {
        if (isset($mapB[$key])) {
            $dot += $val * $mapB[$key];
        }
    }
    return $dot;
}

private static function magnitude($map)
{
    $sum = 0;
    foreach ($map as $val) {
        $sum += $val * $val;
    }
    return sqrt($sum);
}




      // TITLE VERIFY -----------------------------------------------------------------------------------------








       public function undoTemplate(Document $document)
    {
        $this->authorize('update', $document);

        // Restore previous content (if stored)
        $restoredContent = session('previousEditorContent', $document->content);

        // Forget both template and previous content
        session()->forget('templateContent');
        session()->forget('previousEditorContent');

        // Pass the restored content back as flash session (temporary)
        return redirect()
            ->route('documents.edit', $document->id)
            ->with('templateContent', $restoredContent)
            ->with('templateUndone', true); // flag for blade
    }


    public function combineCustom(Request $request, $titleId)
    {
        $orderedIds = $request->input('ordered_ids');
        $includedIds = $request->input('included_ids');
        $customName = $request->input('combined_name');

        if (!$orderedIds || !$includedIds || !$customName) {
            return back()->with('error', 'Please fill all required fields and select at least one chapter.');
        }

        // Only keep the ordered IDs that are also in the included list
        $filteredIds = array_values(array_filter($orderedIds, function ($id) use ($includedIds) {
            return in_array($id, $includedIds);
        }));

        if (empty($filteredIds)) {
            return back()->with('error', 'No chapters selected for combination.');
        }

        $documents = Document::whereIn('id', $filteredIds)->get()->keyBy('id');

        $combinedContent = '';
        foreach ($filteredIds as $docId) {
            if (!isset($documents[$docId])) continue;
            $doc = $documents[$docId];
            $combinedContent .=   "\n\n" . $doc->content . "\n\n";
        }

        $combinedDoc = Document::create([
            'user_id' => auth()->id(),
            'title_id' => $titleId,
            'chapter' => $customName,
            'content' => $combinedContent,
            'format' => 'combined',
            'status' => 'draft',
        ]);

        return redirect()->route('documents.edit', $combinedDoc->id)
                        ->with('success', 'Chapters combined successfully!');
    }


}