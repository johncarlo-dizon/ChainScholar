<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    // Show announcements for all users/advisers
    public function index()
    {
        $announcements = Announcement::latest()->paginate(10);
        return view('announcements.index', compact('announcements'));
    }

    // Admin: Show form to create
    public function create()
    {
        return view('announcements.create');
    }

    // Admin: Store new announcement
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'event_date' => 'nullable|date',
        ]);

        Announcement::create([
            'title' => $request->title,
            'body' => $request->body,
            'event_date' => $request->event_date,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement posted successfully!');
    }

    // Admin: Delete announcement
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('announcements.index')->with('success', 'Announcement deleted.');
    }

    // Admin: Edit form

    public function manage()
    {
        $announcements = Announcement::latest()->paginate(10);
        return view('announcements.manage_announcements', compact('announcements'));
    }
    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }



    // Admin: Update record
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'event_date' => 'nullable|date',
        ]);

        $announcement->update([
            'title' => $request->title,
            'body' => $request->body,
            'event_date' => $request->event_date,
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully!');
    }

}
