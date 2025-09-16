<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    // Show announcements for all users/advisers/students
    public function index(Request $request)
    {
        $tier     = $request->query('tier');
        $audQuery = $request->query('audience'); // optional UI filter

        $query = Announcement::query();

        // Role-based audience gating: guests see only ALL; users see ALL + their role
        $userRole = Auth::check() ? (Auth::user()->role ?? null) : null;

        if ($audQuery && in_array($audQuery, Announcement::AUDIENCES, true)) {
            // explicit filter from UI (admin may want to view specific audience)
            $query->where('audience', $audQuery);
        } else {
            if ($userRole && in_array($userRole, [Announcement::AUD_ADMIN, Announcement::AUD_ADVISER, Announcement::AUD_STUDENT], true)) {
                $query->whereIn('audience', [Announcement::AUD_ALL, $userRole]);
            } else {
                // guests or unknown roles
                $query->where('audience', Announcement::AUD_ALL);
            }
        }

        if ($tier && in_array($tier, Announcement::TIERS, true)) {
            $query->where('tier', $tier);
        }

        // URGENT -> IMPORTANT -> GENERAL; then newest
        $query->orderByRaw("FIELD(tier, 'URGENT','IMPORTANT','GENERAL')")
              ->latest('created_at');

        $announcements = $query->paginate(10)->appends($request->query());

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
            'title'      => 'required|string|max:255',
            'body'       => 'required|string',
            'event_date' => 'nullable|date',
            'tier'       => 'required|in:URGENT,IMPORTANT,GENERAL',
            'audience'   => 'required|in:ALL,STUDENT,ADVISER,ADMIN',
        ]);

        Announcement::create([
            'title'      => $request->title,
            'body'       => $request->body,
            'event_date' => $request->event_date,
            'tier'       => $request->tier,
            'audience'   => $request->audience,
            'user_id'    => Auth::id(),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement posted successfully!');
    }

    // Admin: Delete announcement
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('announcements.index')->with('success', 'Announcement deleted.');
    }

    // Admin: Manage listing
    public function manage(Request $request)
    {
        $tier     = $request->query('tier');
        $audQuery = $request->query('audience');

        $query = Announcement::query();

        if ($audQuery && in_array($audQuery, Announcement::AUDIENCES, true)) {
            $query->where('audience', $audQuery);
        }

        if ($tier && in_array($tier, Announcement::TIERS, true)) {
            $query->where('tier', $tier);
        }

        $query->orderByRaw("FIELD(tier, 'URGENT','IMPORTANT','GENERAL')")
              ->latest('created_at');

        $announcements = $query->paginate(10)->appends($request->query());

        return view('announcements.manage_announcements', compact('announcements'));
    }

    // Admin: Edit form
    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    // Admin: Update record
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string',
            'event_date' => 'nullable|date',
            'tier'       => 'required|in:URGENT,IMPORTANT,GENERAL',
            'audience'   => 'required|in:ALL,STUDENT,ADVISER,ADMIN',
        ]);

        $announcement->update([
            'title'      => $request->title,
            'body'       => $request->body,
            'event_date' => $request->event_date,
            'tier'       => $request->tier,
            'audience'   => $request->audience,
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully!');
    }
}
