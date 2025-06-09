<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
}