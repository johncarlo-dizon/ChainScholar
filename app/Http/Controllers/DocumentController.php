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

class DocumentController extends Controller
{
    use AuthorizesRequests;

public function __construct(private PlagiarismService $plag) {}
 

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
    $plagPct = $this->plag->quickScore($finalHtml, $document);   // in submitFinal
  

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

    // Load the fields you actually need, including 'authors'
    $title = $document->titleRelation()
        ->select('id', 'primary_adviser_id', 'authors')
        ->first();

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

    return view('documents.editor', compact('document', 'adviserNote', 'title'));
}



   






    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $request->validate([
            'content' => 'required'
        ]);

        // Compute plagiarism score but don't block
       $plagPct = $this->plag->quickScore($request->content, $document); // in update

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