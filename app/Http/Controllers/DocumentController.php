<?php

namespace App\Http\Controllers;
use App\Models\Notification;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Title;
use App\Models\AdviserNote;
use Illuminate\Support\Facades\DB;
use App\Services\PlagiarismService;
use App\Models\ResearchPaper;

class DocumentController extends Controller
{
    use AuthorizesRequests;


    public function getContent($id)
{
    $document = Document::findOrFail($id);
    
    // Return the raw HTML content for copying
    return response()->json([
        'content' => $document->content
    ]);
}
 


public function __construct(private PlagiarismService $plag) {}
 
    // CHAIN START
public function showSearchDashboard()
{
    return view('documents.dashboard'); // Initial search screen only
}

public function searchResearch(Request $request)
{
    $queryRaw  = (string) $request->query('query', $request->query('q', ''));
    $queryNorm = $this->searchResearchNormalize($queryRaw); // normalized for tokenization

    // If no query, render explore/empty state
    if ($queryNorm === '') {
        return view('documents.dashboard', [
            'results'   => null,
            'paginator' => null,
            'query'     => $queryRaw,
            'filters'   => null,
        ]);
    }

    // Filters (Scholar-style)
     $type     = $request->query('type', 'all');       // all|title|paper
    $sort     = $request->query('sort', 'relevance'); // relevance|date

    // Sanitize year inputs (accept digits only; allow empty)
    $yf = trim((string) $request->query('year_from', ''));
    $yt = trim((string) $request->query('year_to',   ''));

    $yearFrom = ctype_digit($yf) ? (int) $yf : 0;
    $yearTo   = ctype_digit($yt) ? (int) $yt : 0;

    // Clamp & normalize (e.g., swap if reversed)
    $minYear = 1900;
    $maxYear = (int) now()->year;

    if ($yearFrom && ($yearFrom < $minYear || $yearFrom > $maxYear)) $yearFrom = 0;
    if ($yearTo   && ($yearTo   < $minYear || $yearTo   > $maxYear)) $yearTo   = 0;

    if ($yearFrom && $yearTo && $yearFrom > $yearTo) {
        [$yearFrom, $yearTo] = [$yearTo, $yearFrom];
    }

    $perPage  = (int) $request->query('per_page', 10);
    $page     = (int) $request->query('page', 1);


    // --- Parse query into phrases + tokens (simple AND semantics) ---
    [$phrases, $tokens] = $this->searchResearchExtract($queryRaw);

    $results = [];

    /* -------------------------------------------------------
     * 1) Submitted Titles   (status = submitted)
     *    (search only the Title model.title)
     * ----------------------------------------------------- */
    if ($type === 'all' || $type === 'title') {
        $titlesQ = \App\Models\Title::query()
    ->where('status', 'submitted')
    ->select(['id', 'title', 'authors', 'abstract', 'keywords', 'submitted_at', 'created_at']);
// Year filter at DB level (submitted year)
if ($yearFrom) { $titlesQ->whereYear('submitted_at', '>=', $yearFrom); }
if ($yearTo)   { $titlesQ->whereYear('submitted_at', '<=', $yearTo);   }



        // Build WHERE ... AND (... OR ...) per token/phrase across "title" only
       // Build WHERE ... AND (title|authors|abstract|keywords LIKE ...) for each token/phrase
$titlesQ->where(function ($q) use ($tokens, $phrases) {
    foreach ($tokens as $t) {
        $like = '%' . $t . '%';
        $q->where(function ($qq) use ($like) {
            $qq->orWhere('title',    'LIKE', $like)
               ->orWhere('authors',  'LIKE', $like)
               ->orWhere('abstract', 'LIKE', $like)
               ->orWhere('keywords', 'LIKE', $like);
        });
    }
    foreach ($phrases as $p) {
        $like = '%' . $p . '%';
        $q->where(function ($qq) use ($like) {
            $qq->orWhere('title',    'LIKE', $like)
               ->orWhere('authors',  'LIKE', $like)
               ->orWhere('abstract', 'LIKE', $like)
               ->orWhere('keywords', 'LIKE', $like);
        });
    }
});


        $titles = $titlesQ->get();

        foreach ($titles as $t) {
           $titleText = (string) $t->title;
$authors   = (string) ($t->authors ?? '');
$abstract  = (string) ($t->abstract ?? '');

// Compute keyword score
$score = $this->searchResearchScore($queryRaw, [
    'title'   => $titleText,
    'authors' => $authors,
    'abstract'=> $abstract,
]);

// Highlight
$titleHtml    = $this->searchResearchHighlight($titleText, $phrases, $tokens);
$authorsHtml  = $this->searchResearchHighlight($authors, $phrases, $tokens);
$abstractHtml = $this->searchResearchHighlight($abstract, $phrases, $tokens);

$submittedOrCreated = $t->submitted_at ?: $t->created_at;

$results[] = [
    'type'          => 'title',
    'id'            => $t->id,
    'title'         => $titleText,
    'title_html'    => $titleHtml,
    'authors'       => $authors ?: null,
    'authors_html'  => $authorsHtml ?: null,
    'abstract'      => $abstract ?: null,
    'abstract_html' => $abstractHtml ?: null,
    'open_url'      => route('dashboard.view', $t->id),
    // Use submitted date/year primarily
    'date'          => optional($submittedOrCreated)->toDateString(),
    'year'          => optional($submittedOrCreated)->year,
    'score'         => $score,
];


        }
    }

    /* -------------------------------------------------------
     * 2) ResearchPaper PDFs  (title + authors + abstract)
     * ----------------------------------------------------- */
    if ($type === 'all' || $type === 'paper') {
        $papersQ = \App\Models\ResearchPaper::query()
    ->select(['id','title','authors','abstract','file_path','year','created_at']);
// Year filter at DB level → use the actual `year` column
if ($yearFrom) { $papersQ->where('year', '>=', $yearFrom); }
if ($yearTo)   { $papersQ->where('year', '<=', $yearTo);   }


        // WHERE ... AND (field LIKE for tokens/phrases across title/authors/abstract)
        $papersQ->where(function ($q) use ($tokens, $phrases) {
            foreach ($tokens as $t) {
                $q->where(function ($qq) use ($t) {
                    $like = '%' . $t . '%';
                    $qq->orWhere('title', 'LIKE', $like)
                       ->orWhere('authors', 'LIKE', $like)
                       ->orWhere('abstract', 'LIKE', $like);
                });
            }
            foreach ($phrases as $p) {
                $q->where(function ($qq) use ($p) {
                    $like = '%' . $p . '%';
                    $qq->orWhere('title', 'LIKE', $like)
                       ->orWhere('authors', 'LIKE', $like)
                       ->orWhere('abstract', 'LIKE', $like);
                });
            }
        });

        $papers = $papersQ->get();

        foreach ($papers as $rp) {
            $title   = (string) ($rp->title   ?? '');
            $authors = (string) ($rp->authors ?? '');
            $abstract= (string) ($rp->abstract?? '');

            // Score by occurrences across fields
            $score = $this->searchResearchScore($queryRaw, [
                'title'   => $title,
                'authors' => $authors,
                'abstract'=> $abstract,
            ]);

            // Highlight fields
            $titleHtml    = $this->searchResearchHighlight($title, $phrases, $tokens);
            $authorsHtml  = $this->searchResearchHighlight($authors, $phrases, $tokens);
            $abstractHtml = $this->searchResearchHighlight($abstract, $phrases, $tokens);

         $paperYear = (int) ($rp->year ?? 0);
// For sorting by date, synthesize a date from the model year if present
$paperDate = $paperYear > 0
    ? \Carbon\Carbon::createFromDate($paperYear, 1, 1)->toDateString()
    : optional($rp->created_at)->toDateString();

$results[] = [
    'type'          => 'paper',
    'id'            => $rp->id,
    'paper_id'      => $rp->id,
    'title'         => $title,
    'title_html'    => $titleHtml,
    'authors'       => $authors,
    'authors_html'  => $authorsHtml ?: null,
    'abstract'      => $abstract,
    'abstract_html' => $abstractHtml ?: null,
    'open_url'      => route('papers.view', $rp),
    'file_url'      => $rp->file_path ? \Illuminate\Support\Facades\Storage::url($rp->file_path) : null,
    // Use model year for both the displayed year and sorting (via YYYY-01-01)
    'date'          => $paperDate,
    'year'          => $paperYear ?: optional($rp->created_at)->year,
    'score'         => $score,
];

        }
    }

      // (Removed) Year filter now happens at the DB level for accuracy & performance.


    /* -------------------------------------------------------
     * Sort: 'date' or 'relevance' (keyword score)
     * ----------------------------------------------------- */
    if ($sort === 'date') {
        usort($results, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
    } else {
        usort($results, fn($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));
    }

    /* -------------------------------------------------------
     * Pagination (array → LengthAwarePaginator)
     * ----------------------------------------------------- */
    $total     = count($results);
    $offset    = max(0, ($page - 1) * $perPage);
    $pageItems = array_slice($results, $offset, $perPage);

    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
        $pageItems,
        $total,
        $perPage,
        $page,
        ['path' => url()->current(), 'query' => $request->query()]
    );

    return view('documents.dashboard', [
        'results'   => $pageItems,
        'paginator' => $paginator,
        'query'     => $queryRaw,   // echo original text in UI
        'filters'   => compact('type','sort','yearFrom','yearTo','perPage'),
    ]);
}

/** ---------- Search Helpers (keyword) ---------- */

/**
 * Normalize text: lowercase, strip tags, remove accents, squash spaces.
 */
private function searchResearchNormalize(?string $s): string
{
    $s = (string) $s;
    $s = strip_tags($s);
    $s = mb_strtolower($s, 'UTF-8');
    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    $s = preg_replace('/[^a-z0-9"]+/i', ' ', $s); // keep quotes for phrase parsing
    $s = trim(preg_replace('/\s+/', ' ', $s));
    return $s;
}

/**
 * Extract quoted phrases and remaining tokens.
 * Example:  clinic "management system"  -> phrases:["management system"], tokens:["clinic"]
 */
private function searchResearchExtract(string $raw): array
{
    $phrases = [];
    $tokens  = [];

    // pull quoted phrases from the ORIGINAL (not normalized) text
    preg_match_all('/"([^"]+)"/', $raw, $m);
    if (!empty($m[1])) {
        foreach ($m[1] as $p) {
            $p = trim($p);
            if ($p !== '') $phrases[] = $p;
        }
    }

    // remove phrases from text, then split remaining into tokens
    $stripped = preg_replace('/"[^"]+"/', ' ', $raw);
    $stripped = $this->searchResearchNormalize($stripped);
    if ($stripped !== '') {
        $rawTokens = explode(' ', $stripped);
        $stop = ['the','a','an','of','and','to','in','for','on','with','by','at','from','is','are','be','as','this','that','these','those','using','use','based','system','study']; // mild stopwords; keep domain terms as you like
        foreach ($rawTokens as $t) {
            $t = trim($t);
            if ($t !== '' && !in_array($t, $stop, true)) $tokens[] = $t;
        }
    }

    // normalize phrases for LIKE matching but keep original spacing
    $phrases = array_map(fn($p) => trim($p), $phrases);

    return [$phrases, $tokens];
}

/**
 * Simple keyword score = sum of case-insensitive occurrences across fields,
 * with small weights (title > authors > abstract).
 */
private function searchResearchScore(string $queryRaw, array $fields): int
{
    [$phrases, $tokens] = $this->searchResearchExtract($queryRaw);

    $score = 0;
    $weights = ['title' => 5, 'authors' => 3, 'abstract' => 1];

    foreach ($fields as $field => $text) {
        $txt = (string) ($text ?? '');
        if ($txt === '') continue;

        // phrases
        foreach ($phrases as $p) {
            $score += substr_count(mb_strtolower($txt), mb_strtolower($p)) * $weights[$field];
        }
        // tokens
        foreach ($tokens as $t) {
            $score += substr_count(mb_strtolower($txt), mb_strtolower($t)) * $weights[$field];
        }
    }
    return $score;
}

/**
 * Highlight phrases and tokens in a given text with <mark>.
 * Returns HTML-safe string.
 */
private function searchResearchHighlight(string $text, array $phrases, array $tokens): string
{
    if ($text === '') return '';

    $escaped = e($text);

    // Sort longer phrases first to avoid splitting them by token highlights
    usort($phrases, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));
    usort($tokens,  fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

    $patterns = [];

// phrases (allow spaces inside, case-insensitive)
foreach ($phrases as $p) {
    $p = trim($p);
    if ($p === '') continue;
    $patterns[] = preg_quote($p, '/');
}

// tokens
foreach ($tokens as $t) {
    $t = trim($t);
    if ($t === '') continue;
    $patterns[] = preg_quote($t, '/');
}


    if (!$patterns) return $escaped;

    $regex = '/(' . implode('|', $patterns) . ')/iu';

    $highlighted = preg_replace(
        $regex,
        '<mark class="bg-yellow-200 px-0.5 rounded">$1</mark>',
        $escaped
    );

    return $highlighted ?? $escaped;
}
// CHAIN END


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
            ->where('status', 'submitted')
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
        $title = Title::findOrFail($id);

        // Reset title status and submitted_at
        $title->update([
            'status' => 'in_advising',
            'submitted_at' => null,
        ]);

        return redirect()->back()->with('success', 'Submission has been cancelled.');
    }


    

   public function submitFinal(Request $request, $title_id)
    {
    $request->validate([
        'finaldocument_id'     => 'required|exists:documents,id',
        'authors'              => 'required|string',
        'abstract'             => 'required|string',
        'research_type'        => 'required|string',
        'final_content'        => 'nullable|string',
        'plagiarism_internal'  => 'nullable|numeric|min:0|max:100',
        'plagiarism_external'  => 'nullable|numeric|min:0|max:100',
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
    // Internal score (fallback if no posted value)
    $plagPct  = $this->plag->quickScore($finalHtml, $document);

    // Use the posted numbers if present (set by hidden inputs on upload)
    $internal = is_numeric($request->plagiarism_internal) ? (float) $request->plagiarism_internal : (float) $plagPct;
    $external = is_numeric($request->plagiarism_external) ? (float) $request->plagiarism_external : null;

  

    DB::transaction(function () use ($document, $title, $finalHtml, $plagPct, $internal, $external, $request) {

        // ✅ Update the chosen chapter/content & similarity
       $document->update([
    'content'              => $finalHtml,
    // keep legacy field for compatibility
    'plagiarism_score'     => round((float)$internal, 2),
    // new detailed fields
    'plagiarism_internal'  => isset($internal) ? round((float)$internal, 2) : null,
    'plagiarism_external'  => isset($external) ? round((float)$external, 2) : null,
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
        ->with('status', "Final document submitted! Similarity: {$plagPct}%");
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

        $title = $document->titleRelation()
            ->select('id', 'title', 'primary_adviser_id', 'authors', 'status')
            ->first();

        // Gate: only editable after admin approval
        if (!$title || $title->status !== 'in_advising') {
            return redirect()
                ->route('titles.chapters', $document->title_id)
                ->with('error', 'Editing is locked until the admin approves your adviser assignment.');
        }

        $adviserNote = null;
        if ($title->primary_adviser_id) {
            $adviserNote = \App\Models\AdviserNote::with('adviser')
                ->where('document_id', $document->id)
                ->where('adviser_id', $title->primary_adviser_id)
                ->first();
        } else {
            $adviserNote = \App\Models\AdviserNote::with('adviser')
                ->where('document_id', $document->id)
                ->latest('updated_at')
                ->first();
        }

        return view('documents.editor', compact('document', 'adviserNote', 'title'));
    }




   






    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        // Guard: must be admin-approved
        $title = $document->titleRelation()->select('id','status')->first();
        if (!$title || $title->status !== 'in_advising') {
            return back()->with('error', 'Editing is locked until the admin approves your adviser assignment.');
        }

        $request->validate(['content' => 'required']);

        $plagPct = $this->plag->quickScore($request->content, $document);

        $document->update([
            'content' => $request->content,
            'plagiarism_score' => $plagPct,
        ]);

      return back()->with('status', "Chapter updated successfully! Similarity: {$plagPct}%");

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