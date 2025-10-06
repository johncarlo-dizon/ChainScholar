<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Title;
use App\Models\Notification;
use App\Models\Announcement;
use App\Models\AdviserRequest;
use App\Models\ResearchPaper;

class UserController extends Controller
{
    // Display all users
    // app/Http/Controllers/UserController.php

public function index(Request $request)
{
    $q      = trim((string) $request->input('q', ''));
    $role   = (string) $request->input('role', '');
    $status = (string) $request->input('status', ''); // "", "enabled", "disabled"


    // Build query
    $query = User::query()
        ->where('id', '!=', auth()->id()); // exclude current user

    // Text search: id / name / email
    if ($q !== '') {
        $query->where(function ($sub) use ($q) {
            $sub->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%");

            // If q is numeric, also allow exact ID match
            if (ctype_digit($q)) {
                $sub->orWhere('id', (int) $q);
            }
        });
    }

    // Role filter (skip if blank or 'ALL')
    if ($role !== '' && strtoupper($role) !== 'ALL') {
        $query->where('role', $role);
    }
    // Status filter (enabled/disabled)
    if ($status === 'enabled') {
        $query->where('is_active', true);
    } elseif ($status === 'disabled') {
        $query->where('is_active', false);
    }


    $users = $query
        ->orderByDesc('id')
        ->paginate(perPage: 5)                 // keep your page size
        ->appends($request->query()); // keep filters in pagination links

    // Get distinct roles for dropdown
    $roles = User::query()
        ->select('role')
        ->whereNotNull('role')
        ->distinct()
        ->orderBy('role')
        ->pluck('role');

    return view('admin.users.index', [
        'users'   => $users,
        'roles'   => $roles,
       'filters' => ['q' => $q, 'role' => $role, 'status' => $status],
    ]);
}


    // Show create user form
    public function create()
    {
        return view('admin.users.create');
    }


    public function showDashboard(Request $request)
    {
        $user = $request->user();
        $now  = now();

        /** ---------- Sidebar needs ---------- */
        $unreadCount = Notification::where('user_id', $user->id)->where('is_read', false)->count();
        $notifications = Notification::where('user_id', $user->id)->latest()->limit(10)->get();

        // Announcements visible to this role (for sidebar badge)
        $audForRole = match ($user->role) {
            'ADMIN'   => [Announcement::AUD_ALL, Announcement::AUD_ADMIN],
            'ADVISER' => [Announcement::AUD_ALL, Announcement::AUD_ADVISER],
            default   => [Announcement::AUD_ALL, Announcement::AUD_STUDENT],
        };
        $announcementsCount = Announcement::whereIn('audience', $audForRole)->count();

        /** ---------- Admin dashboard data (no documents list) ---------- */
        $awaitingAdminCount = Title::where('status', 'awaiting_admin')->count();

        // Users & roles
        $metrics = [
            'users_total'  => User::count(),
            'admins'       => User::where('role', 'ADMIN')->count(),
            'advisers'     => User::where('role', 'ADVISER')->count(),
            'students'     => User::where('role', 'STUDENT')->count(),

            // Titles focus
            'titles_total'        => Title::count(),
            'titles_submitted'    => Title::where('status', 'submitted')->count(),
            'titles_awaiting'     => $awaitingAdminCount,
            'titles_in_advising'  => Title::where('status', 'in_advising')->count(),
            'titles_with_adviser' => Title::whereNotNull('primary_adviser_id')->count(),
            'titles_final'        => Title::whereNotNull('final_document_id')->count(), // approved/finalized proxy
        ];

        // Research Papers (uploaded PDFs)
        $metrics['papers_total'] = ResearchPaper::count();

        // Recent Titles (for activity)
        $recentTitles = Title::with(['owner', 'primaryAdviser'])
            ->latest('created_at')->limit(8)->get();

        // Announcements block for dashboard (admin sees everything)
        $announcementsTotal = Announcement::count();
        $annByTier = [
            'urgent'    => Announcement::where('tier', Announcement::TIER_URGENT)->count(),
            'important' => Announcement::where('tier', Announcement::TIER_IMPORTANT)->count(),
            'general'   => Announcement::where('tier', Announcement::TIER_GENERAL)->count(),
        ];
        $upcomingAnnouncements = Announcement::whereDate('event_date', '>=', $now->toDateString())
            ->orderBy('event_date', 'asc')
            ->limit(6)
            ->get();
        $recentAnnouncements = Announcement::latest()->limit(6)->get();

        // Recent Research Papers
        $recentPapers = ResearchPaper::with('user')->latest()->limit(6)->get();

        // Pending adviser requests
        $pendingAdviserRequests = AdviserRequest::where('status', 'pending')->count();

        return view('admin.index', [
            // sidebar
            'unreadCount'        => $unreadCount,
            'notifications'      => $notifications,
            'announcementsCount' => $announcementsCount,
            'awaitingAdminCount' => $awaitingAdminCount,

            // dashboard
            'metrics'               => $metrics,
            'recentTitles'          => $recentTitles,
            'announcementsTotal'    => $announcementsTotal,
            'annByTier'             => $annByTier,
            'upcomingAnnouncements' => $upcomingAnnouncements,
            'recentAnnouncements'   => $recentAnnouncements,
            'recentPapers'          => $recentPapers,
            'pendingAdviserRequests'=> $pendingAdviserRequests,
        ]);
    }

    // Store new user
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);


        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    // Show edit user form
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // Update existing user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users,name,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|max:255',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];


        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

  
    public function toggle(User $user)
    {
        // Prevent self-disable to avoid locking yourself out (optional safety)
        if (auth()->id() === $user->id) {
            return back()->with('success', 'You cannot change your own status.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', $user->is_active ? 'User enabled.' : 'User disabled.');
    }


    // Delete user
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}