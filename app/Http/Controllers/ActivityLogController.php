<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Title;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
   public function index(Request $request)
    {
        $user    = $request->user();
        $isAdmin = $user->role === 'ADMIN';

        // Per-page: allowlist
        $allowedPerPage = [10, 20, 50, 100];
        $perPage = (int) $request->query('per_page', default: 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

    $query = ActivityLog::with([
    'user',
    'subject' => function ($m) {
        $m->morphWith([
            \App\Models\Title::class             => ['owner', 'adviser'],
            \App\Models\Document::class          => ['title'],
            \App\Models\ResearchPaper::class     => ['user'],
            \App\Models\AdviserRequest::class    => ['title', 'adviser'],
            \App\Models\Announcement::class      => [],
            \App\Models\BlockchainRequest::class => ['paper.user'],
            \App\Models\User::class              => [], // ⬅️ add: user profile/management logs
        ]);
    },
])->latest();




        // Visibility scope
        if (!$isAdmin) {
            $query->where('user_id', $user->id);

            // If you later want related-title visibility, keep your commented blocks here.
        }

   
        // Everyone can filter by action (exact) + dates
        if ($request->filled('action')) {
            $action = (string) $request->query('action');
            $query->where('action', $action);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->date('to')->endOfDay());
        }

        // Admin-only filters: ignore if non-admin even if query params exist
        if ($isAdmin && $request->filled('who')) {
            $who = (string) $request->query('who');
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$who}%"));
        }
        if ($isAdmin && $request->filled('role')) {
            $roleFilter = (string) $request->query('role');
            $query->where('role', $roleFilter);
        }

        $logs = $query->paginate($perPage)->withQueryString();
// Build dropdown of available actions (distinct)
        $actions = ActivityLog::query()
            ->select('action')
            ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->all();

        return view('activity.index', [
            'logs'    => $logs,
            'isAdmin' => $isAdmin,
            'perPage' => $perPage,
            'actions' => $actions,
        ]);

    }
}
