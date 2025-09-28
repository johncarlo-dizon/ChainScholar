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

public function __construct(private PlagiarismService $plag) {}
 
    // CHAIN START
    public function showSearchDashboard()
    {
        return view('documents.dashboard'); // Initial search screen only
    }

public function searchResearch(Request $request)
{
    $queryRaw  = (string) $request->query('query', $request->query('q', ''));
    $query     = $this->searchResearchNormalize($queryRaw); // use helper with the searchResearch* prefix
    $threshold = 0.30; // tune 0.28–0.38 for recall vs precision

    // If no query, render explore/empty state
    if ($query === '') {
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
    $yearFrom = (int) $request->query('year_from', 0);
    $yearTo   = (int) $request->query('year_to', 0);
    $perPage  = (int) $request->query('per_page', 10);
    $page     = (int) $request->query('page', 1);

    $results = [];

    /* -------------------------------------------------------
     * 1) Submitted Titles   (status = submitted)
     * ----------------------------------------------------- */
    $titles = \App\Models\Title::query()
        ->where('status', 'submitted')
        ->select(['id', 'title', 'created_at'])
        ->get();

    foreach ($titles as $t) {
        $titleText = (string) $t->title;
        if ($titleText === '') continue;

        // Fuzzy, typo/phonetic tolerant scorer
        $sim = $this->searchResearchFuzzyScore($queryRaw, $titleText);

        if ($sim >= $threshold) {
            $item = [
                'type'       => 'title',
                'id'         => $t->id,
                'title'      => $titleText,
                'authors'    => null,
                'abstract'   => null,
                'similarity' => $sim,
                'file_url'   => null,
                'date'       => optional($t->created_at)->toDateString(),
                'year'       => optional($t->created_at)->year,
            ];
            if ($type === 'all' || $type === 'title') {
                $results[] = $item;
            }
        }
    }

    /* -------------------------------------------------------
     * 2) ResearchPaper PDFs  (title + authors + abstract)
     * ----------------------------------------------------- */
    $papers = \App\Models\ResearchPaper::query()
        ->select(['id','title','authors','abstract','file_path','created_at'])
        ->get();

    foreach ($papers as $rp) {
        $haystack = trim(implode(' ', array_filter([
            (string) $rp->title,
            (string) $rp->authors,
            (string) $rp->abstract,
        ])));

        if ($haystack === '') continue;

        $sim = $this->searchResearchFuzzyScore($queryRaw, $haystack);

        if ($sim >= $threshold) {
            $item = [
                'type'       => 'paper',
                'id'         => $rp->id,
                'title'      => (string) $rp->title ?: '(Untitled PDF)',
                'authors'    => (string) $rp->authors ?: null,
                'abstract'   => (string) $rp->abstract ?: null,
                'similarity' => $sim,
                'file_url'   => $rp->file_path ? \Illuminate\Support\Facades\Storage::url($rp->file_path) : null,
                'date'       => optional($rp->created_at)->toDateString(),
                'year'       => optional($rp->created_at)->year,
            ];
            if ($type === 'all' || $type === 'paper') {
                $results[] = $item;
            }
        }
    }

    /* -------------------------------------------------------
     * Year filter (if provided)
     * ----------------------------------------------------- */
    if ($yearFrom || $yearTo) {
        $results = array_values(array_filter($results, function ($r) use ($yearFrom, $yearTo) {
            $y = (int) ($r['year'] ?? 0);
            if (!$y) return false;
            if ($yearFrom && $y < $yearFrom) return false;
            if ($yearTo && $y > $yearTo) return false;
            return true;
        }));
    }

    /* -------------------------------------------------------
     * Sort: 'date' or 'relevance' (similarity)
     * ----------------------------------------------------- */
    if ($sort === 'date') {
        usort($results, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
    } else {
        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
    }

    /* -------------------------------------------------------
     * Pagination (array → LengthAwarePaginator)
     * ----------------------------------------------------- */
    $total    = count($results);
    $offset   = max(0, ($page - 1) * $perPage);
    $chunks   = array_slice($results, $offset, $perPage);
    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
        $chunks,
        $total,
        $perPage,
        $page,
        ['path' => url()->current(), 'query' => $request->query()]
    );

    return view('documents.dashboard', [
        'results'   => $chunks,     // current page only
        'paginator' => $paginator,  // for links()
        'query'     => $queryRaw,   // echo original text in UI
        'filters'   => compact('type','sort','yearFrom','yearTo','perPage'),
    ]);
}



    /** ---------- Search Helpers (searchResearch* prefix) ---------- */

    /** Normalize text: lowercase, strip tags, remove accents, squash spaces */
    private function searchResearchNormalize(?string $s): string
    {
        $s = (string) $s;
        $s = strip_tags($s);
        $s = mb_strtolower($s, 'UTF-8');
        // remove accents
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        // keep letters/numbers and spaces
        $s = preg_replace('/[^a-z0-9]+/i', ' ', $s);
        // collapse spaces
        $s = trim(preg_replace('/\s+/', ' ', $s));
        return $s;
    }

    /** Tokenize normalized text to terms (keeps simple words only) */
    private function searchResearchTokens(string $s): array
    {
        $s = $this->searchResearchNormalize($s);
        if ($s === '') return [];
        $raw = explode(' ', $s);
        // basic stopwords to reduce noise
        $stop = ['the','a','an','of','and','to','in','for','on','with','by','at','from','is','are','be','as','this','that','these','those','using','use','based'];
        return array_values(array_filter($raw, fn($t) => $t !== '' && !in_array($t, $stop, true)));
    }

    /** Build n-gram shingles (character-level) for typo tolerance */
    private function searchResearchShingles(string $s, int $n = 3): array
    {
        $s = $this->searchResearchNormalize($s);
        if (strlen($s) < $n) return $s === '' ? [] : [$s];
        $ngrams = [];
        for ($i = 0; $i <= strlen($s) - $n; $i++) {
            $ngrams[] = substr($s, $i, $n);
        }
        return $ngrams;
    }

    /** Cosine similarity on token frequency vectors */
    private function searchResearchCosineTokens(string $a, string $b): float
    {
        $ta = $this->searchResearchTokens($a);
        $tb = $this->searchResearchTokens($b);
        if (!$ta || !$tb) return 0.0;

        $fa = array_count_values($ta);
        $fb = array_count_values($tb);
        $allKeys = array_unique(array_merge(array_keys($fa), array_keys($fb)));

        $dot = 0.0; $na = 0.0; $nb = 0.0;
        foreach ($allKeys as $k) {
            $va = $fa[$k] ?? 0;
            $vb = $fb[$k] ?? 0;
            $dot += $va * $vb;
            $na += $va * $va;
            $nb += $vb * $vb;
        }
        if ($na == 0.0 || $nb == 0.0) return 0.0;
        return $dot / (sqrt($na) * sqrt($nb));
    }

    /** Jaccard similarity on character n-grams (default 3-grams) */
    private function searchResearchJaccardNgrams(string $a, string $b, int $n = 3): float
    {
        $A = $this->searchResearchShingles($a, $n);
        $B = $this->searchResearchShingles($b, $n);
        if (!$A || !$B) return 0.0;
        $setA = array_values(array_unique($A));
        $setB = array_values(array_unique($B));
        $intersect = array_intersect($setA, $setB);
        $union = array_unique(array_merge($setA, $setB));
        return count($union) ? count($intersect) / count($union) : 0.0;
    }

    /** Levenshtein-based similarity for overall string closeness */
    private function searchResearchLevenshteinSim(string $a, string $b): float
    {
        $na = $this->searchResearchNormalize($a);
        $nb = $this->searchResearchNormalize($b);
        if ($na === '' || $nb === '') return 0.0;
        $dist = levenshtein($na, $nb);
        $maxLen = max(strlen($na), strlen($nb));
        return $maxLen ? max(0.0, 1.0 - ($dist / $maxLen)) : 0.0;
    }

    /** Phonetic boost using metaphone on individual tokens (for homophones) */
    private function searchResearchPhoneticBoost(string $a, string $b): float
    {
        $ta = $this->searchResearchTokens($a);
        $tb = $this->searchResearchTokens($b);
        if (!$ta || !$tb) return 0.0;

        $ma = array_map(fn($t) => metaphone($t), $ta);
        $mb = array_map(fn($t) => metaphone($t), $tb);

        $ma = array_values(array_filter($ma));
        $mb = array_values(array_filter($mb));
        if (!$ma || !$mb) return 0.0;

        $inter = array_intersect($ma, $mb);
        $union = array_unique(array_merge($ma, $mb));
        $jac = count($union) ? count($inter) / count($union) : 0.0;
        // small boost (not dominant)
        return min(0.15, $jac * 0.2);
    }

    /**
     * Fuzzy score (0..1) blending:
     * - cosine(tokens)
     * - jaccard(3-grams)
     * - levenshtein similarity
     * then adds a small phonetic boost
     */
    private function searchResearchFuzzyScore(string $query, string $haystack): float
    {
        $cos = $this->searchResearchCosineTokens($query, $haystack);
        $jac = $this->searchResearchJaccardNgrams($query, $haystack, 3);
        $lev = $this->searchResearchLevenshteinSim($query, $haystack);

        // take a weighted max to be tolerant for short vs long strings
        $core = max($cos, $jac * 0.9, $lev * 0.85);

        // phonetic micro-boost for sound-alike matches
        $boost = $this->searchResearchPhoneticBoost($query, $haystack);

        $score = min(1.0, $core + $boost);
        return $score;
    }


    // CHAIN SEARCH ENDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDD

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