<?php

// app/Http/Controllers/StudentNoteController.php
namespace App\Http\Controllers;

use App\Models\StudentNote;
use App\Models\Title;
use App\Models\Document;
use App\Models\Notification;
use Illuminate\Http\Request;

class StudentNoteController extends Controller
{
    public function save(Request $request, Title $title, Document $document)
    {
        // Security: student must own title; document must belong to title
        abort_if((int)$title->owner_id !== (int)$request->user()->id, 403);
        abort_if((int)$document->title_id !== (int)$title->id, 404);

        $data = $request->validate([
            'message' => 'required|string',
        ]);

        if (!$title->primary_adviser_id) {
            return back()->with('error', 'No adviser assigned to this title yet.');
        }

        // 🔁 One note per (student × document) — create or update (overwrite content)
        $note = StudentNote::firstOrNew(
            [
                'document_id' => $document->id,
                'student_id'  => $request->user()->id,
            ],
            [
                'title_id'    => $title->id,
                'adviser_id'  => $title->primary_adviser_id,
            ]
        );

        $note->content = $data['message']; // ← overwrite (e.g., “hello sir” replaces “hello”)
        $note->save();

        // Notify adviser on create OR update (optional: only on changes)
        Notification::create([
            'user_id' => $title->primary_adviser_id,
            'title'   => 'Student updated their note',
            'message' => $document->chapter.' | '.$title->title,
            'is_read' => false,
        ]);

        return back()->with('success', $note->wasRecentlyCreated ? 'Message sent.' : 'Message updated.');
    }
}
