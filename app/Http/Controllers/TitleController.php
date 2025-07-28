<?php

namespace App\Http\Controllers;
use App\Models\Title;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Notification;

class TitleController extends Controller
{
    //
    public function verifyForm()
    {
        return view('documents.verify');
    }

  
    public function verifyAndProceed(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
    
        $title = Title::create([
            'user_id' => auth()->id(),
            'title' => $request->title
        ]);
    
        // Create default chapters
        $defaultChapters = [
            'Chapter 1',
            'Chapter 2',
            'Chapter 3',
            'Chapter 4',
            'Chapter 5',
        ];
    
        foreach ($defaultChapters as $chapterName) {
            Document::create([
                'user_id' => auth()->id(),
                'title_id' => $title->id,
                'chapter' => $chapterName,
                'content' => '',
                'format' => 'separate',
            ]);
        }
    
        // Create notification
        Notification::create([
            'user_id' => auth()->id(),
            'title' => 'Title Verified',
            'message' => 'Your title and default chapters have been successfully created.',
            'is_read' => false,
        ]);
    
        return redirect()
        ->route('titles.chapters', $title->id)
        ->with('status', 'Title and chapters created successfully!');
    
    }





    public function showChapters($titleId)
    {
        $title = Title::with('documents')->findOrFail($titleId);
        return view('documents.chapters', compact('title'));
    }


    public function index()
    {
        $titles = auth()->user()
            ->titles()
            ->whereIn('status', ['draft']) // ✅ Only draft or returned
            ->latest()
            ->get();
    
        return view('documents.index', compact('titles'));
    }
    
    
    

    public function destroy($id)
    {
    $title = Title::with('documents')->where('user_id', auth()->id())->findOrFail($id);

    // Delete related chapters/documents
    $title->documents()->delete();
    
    // Delete title
    $title->delete();

    return redirect()->route('titles.index')->with('status', 'Title and its chapters deleted successfully.');
}


}
