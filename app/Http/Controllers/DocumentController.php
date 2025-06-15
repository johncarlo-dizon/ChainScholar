<?php

namespace App\Http\Controllers;
use App\Models\Notification;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DocumentController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
     
        $documents = auth()->check() 
           ? auth()->user()->documents()->latest()->get()
            : collect(); 

        return view('documents.index', compact('documents'));
    }


    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        
        // Optional: check if the authenticated user owns the document
        // if (auth()->id() !== $document->user_id) {
        //     abort(403);
        // }

        $document->delete();

        return redirect()->route('documents.index')
                        ->with('success', 'Document deleted successfully.');
    }


 

    public function create()
    {
        return view('documents.editor');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required'
        ]);

        Document::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $request->content
        ]);

        Notification::create([
        'user_id' => auth()->id(),
        'title' => 'Document Submitted',
        'message' => 'Your document "' . $request->title . '" has been submitted and is now pending approval.',
        ]);


        return redirect()->route('documents.index')->with('success', 'Document saved!');
    }

    public function edit(Document $document)
    {
        $this->authorize('update', $document);
        return view('documents.editor', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required'
        ]);

        $document->update([
            'title' => $request->title,
            'content' => $request->content
        ]);

        return redirect()->route('documents.index')->with('success', 'Document updated!');
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


    public function showDashboard(){
        return view('documents.dashboard');
    }
    public function showVerify(){
        return view('documents.verify');
    }

    public function checkTitleSimilarity(Request $request)
    {
    $inputTitle = $request->input('title');
    $documentId = $request->input('document_id'); // Accept the current doc ID (optional)

    $documents = Document::all();

    $similarities = [];
    foreach ($documents as $doc) {
        // Skip if the current document matches the one being edited
        if ($documentId && $doc->id == $documentId) {
            continue;
        }

        similar_text(strtolower($inputTitle), strtolower($doc->title), $percent);
        $similarities[] = [
            'existing_title' => $doc->title,
            'similarity' => round($percent, 2)
        ];
    }

    // Find the most similar title
    $maxSimilarity = collect($similarities)->max('similarity') ?? 0;

    return response()->json([
        'max_similarity' => $maxSimilarity,
        'similarities' => $similarities,
        'approved' => $maxSimilarity < 30
    ]);
    }


    
    public function checkWebTitleSimilarity(Request $request)
    {
        $title = $request->input('title');
        $response = Http::get('https://api.semanticscholar.org/graph/v1/paper/search', [
            'query' => $title,
            'fields' => 'title',
            'limit' => 5,
        ]);

        $items = $response->json('data', []);
        $similarities = [];

        foreach ($items as $item) {
            similar_text(strtolower($title), strtolower($item['title']), $percent);
            $similarities[] = $percent;
        }

        $max = count($similarities) ? round(max($similarities), 2) : 0;
        return response()->json([
            'max_similarity' => $max,
            'approved' => $max < 30,
            'results' => $items,
        ]);
    }

}