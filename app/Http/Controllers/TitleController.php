<?php

namespace App\Http\Controllers;
use App\Models\Title;
use Illuminate\Http\Request;

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

        return redirect()->route('titles.chapters', $title->id);
    }

    public function showChapters($titleId)
    {
        $title = Title::with('documents')->findOrFail($titleId);
        return view('documents.chapters', compact('title'));
    }

    public function index()
    {
        $titles = auth()->user()->titles()->latest()->get();
        return view('documents.index', compact('titles'));
    }

    public function destroy($id)
    {
    $title = Title::with('documents')->where('user_id', auth()->id())->findOrFail($id);

    // Delete related chapters/documents
    $title->documents()->delete();
    
    // Delete title
    $title->delete();

    return redirect()->route('titles.index')->with('success', 'Title and its chapters deleted successfully.');
}


}
