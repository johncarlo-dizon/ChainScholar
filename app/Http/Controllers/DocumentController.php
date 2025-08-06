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


class DocumentController extends Controller
{
    use AuthorizesRequests;


    public function showSearchDashboard()
    {
        return view('documents.dashboard'); // Initial search screen only
    }

    public function searchResearch(Request $request)
    {
        $query = $request->input('query');

        $approvedTitles = Title::with('user')
            ->where('status', 'approved')
            ->where('user_id', '!=', auth()->id())
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
        $query = auth()->user()->titles()->whereIn('status', ['pending', 'approved', 'returned']);
    
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        $titles = $query->latest()->get();
    
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
            'abstract' => 'required|string',
            'keywords' => 'required|string',
            'category' => 'required|string',
            'sub_category' => 'nullable|string',
            'research_type' => 'required|string',
        ]);

        $document = Document::findOrFail($request->finaldocument_id);
        $document->update([
            'content' => $request->final_content,
        ]);
    
        $title = \App\Models\Title::findOrFail($title_id);
    
        $title->update([
            'finaldocument_id' => $request->finaldocument_id,
            'abstract' => $request->abstract,
            'keywords' => $request->keywords,
            'category' => $request->category,
            'sub_category' => $request->sub_category,
            'research_type' => $request->research_type,
            'status' => 'pending', // ✅ set status to pending
            'submitted_at' => now(), // ✅ set submission time
        ]);
    
        return redirect()->route('titles.index')->with('success', 'Final document submitted!');
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
       
        return view('documents.editor', compact('document'));
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









    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $request->validate([
            'content' => 'required'
        ]);

        $document->update([
            'content' => $request->content
        ]);

        return redirect()->route('titles.chapters', $document->title_id)
                        ->with('success', 'Chapter updated successfully!');
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








    public function showVerify(){
        return view('documents.verify');
    }





    //DocumentController.php
    // TITLE VERIFY -----------------------------------------------------------------------------------------


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
    $cacheKey = 'web_similarity_' . md5($inputTitle);

    $data = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($inputTitle) {
        $response = Http::get('https://api.semanticscholar.org/graph/v1/paper/search', [
            'query' => $inputTitle,
            'fields' => 'title',
            'limit' => 5,
        ]);

        $items = $response->json('data', []);
        $similarities = [];

        foreach ($items as $item) {
            $webTitle = strtolower($item['title']);
            $score = self::cosineSimilarity($inputTitle, $webTitle);

            $similarities[] = [
                'title' => $item['title'],
                'similarity' => round($score * 100, 2),
            ];
        }

        usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        $max = $similarities[0]['similarity'] ?? 0;

        return [
            'max_similarity' => $max,
            'approved' => $max < 30,
            'results' => $similarities,
        ];
    });

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

}