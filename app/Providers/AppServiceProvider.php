<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Models\BlockchainRequest;
use App\Models\Notification;
use App\Models\Title;
use App\Models\Announcement;
use App\Models\ResearchPaper;
use App\Models\Document;
use App\Models\AdviserRequest;
use Illuminate\Support\Facades\Gate;
use App\Services\Activity;
use App\Policies\ResearchPaperPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {

        // Map the policy so Gate::authorize('update', $paper) works
        Gate::policy(ResearchPaper::class, ResearchPaperPolicy::class);

        // (Optional) Let ADMIN bypass all checks globally
        Gate::before(function ($user, $ability) {
            return ($user->role ?? null) === 'ADMIN' ? true : null;
        });

        /** ---------------- Blade helpers ---------------- */
        Blade::if('admin', function () {
            return Auth::check() && Auth::user()->role === 'ADMIN';
        });
      

        /** ---------------- Global view data (notifications) ---------------- */
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $userId = Auth::id();

                $notifications = Notification::where('user_id', $userId)
                    ->latest()
                    ->take(5)
                    ->get();

                $unreadCount = Notification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();

                $view->with([
                    'notifications' => $notifications,
                    'unreadCount'   => $unreadCount,
                ]);
            }
        });

        /** ---------------- Sidebar: awaiting admin count ---------------- */
        View::composer('components.sidebar', function ($view) {
            $count = 0;
            if (auth()->check() && auth()->user()->role === 'ADMIN') {
                $count = Title::where('status', 'awaiting_admin')->count();
            }
            $view->with('awaitingAdminCount', $count);
        });

        /** ---------------- AUTH EVENTS ---------------- */
        Event::listen(Login::class, function (Login $event) {
            Activity::log('auth.login', null, [
                'guard' => $event->guard ?? 'web',
            ], $event->user);
        });

        Event::listen(Logout::class, function (Logout $event) {
            Activity::log('auth.logout', null, [
                'guard' => $event->guard ?? 'web',
            ], $event->user);
        });

        /** ---------------- MODEL EVENT LOGS ---------------- */

        /* ---- TITLES ---- */
        Title::created(function (Title $t) {
            Activity::log('title.created', $t, [
                'title'  => $t->title,
                'status' => (string) $t->status,
                'owner'  => $t->owner?->name,
            ]);
        });

        Title::updated(function (Title $t) {
            $orig = $t->getOriginal();

            if (($orig['status'] ?? null) !== $t->status) {
                Activity::log('title.status_changed', $t, [
                    'from' => (string) ($orig['status'] ?? ''),
                    'to'   => (string) $t->status,
                ]);
            }
                // Granular: admin lane decisions
            $toStatus = (string) $t->status;
            if ($toStatus === 'approved') {
                Activity::log('title.admin.approved', $t, [
                    'title'  => $t->title,
                    'owner'  => $t->owner?->name,
                ]);
            } elseif (in_array($toStatus, ['returned','rejected'], true)) {
                Activity::log('title.admin.rejected', $t, [
                    'title'  => $t->title,
                    'owner'  => $t->owner?->name,
                    'reason' => (string) ($t->review_comments ?? ''),
                ]);
            } elseif ($toStatus === 'awaiting_admin' && (($orig['status'] ?? null) !== 'awaiting_admin')) {
                Activity::log('title.awaiting_admin', $t, [
                    'title'  => $t->title,
                    'owner'  => $t->owner?->name,
                    'adviser_id' => (int) ($t->primary_adviser_id ?? 0),
                ]);
            }


            if (($orig['primary_adviser_id'] ?? null) !== $t->primary_adviser_id) {
                Activity::log('title.adviser_assigned', $t, [
                    'from' => (int) ($orig['primary_adviser_id'] ?? 0),
                    'to'   => (int) ($t->primary_adviser_id ?? 0),
                ]);
            }

            if (($orig['final_document_id'] ?? null) !== $t->final_document_id) {
                Activity::log('title.final_document_set', $t, [
                    'final_document_id' => (int) ($t->final_document_id ?? 0),
                ]);
            }
        });

        Title::deleted(function (Title $t) {
            Activity::log('title.deleted', $t, [
                'title' => $t->title,
            ]);
        });

        /* ---- ANNOUNCEMENTS ---- */
        Announcement::created(function (Announcement $a) {
            Activity::log('announcement.created', $a, [
                'tier'     => (string) $a->tier,
                'audience' => (string) $a->audience,
            ]);
        });

        Announcement::updated(function (Announcement $a) {
            Activity::log('announcement.updated', $a, [
                'tier'     => (string) $a->tier,
                'audience' => (string) $a->audience,
            ]);
        });

        Announcement::deleted(function (Announcement $a) {
            Activity::log('announcement.deleted', $a, [
                'title' => $a->title,
            ]);
        });

        /* ---- RESEARCH PAPERS ---- */
        ResearchPaper::created(function (ResearchPaper $p) {
            Activity::log('paper.uploaded', $p, [
                'title'   => $p->title,
                'program' => $p->program,
                'user'    => $p->user?->name,
            ]);
        });

        ResearchPaper::deleted(function (ResearchPaper $p) {
            Activity::log('paper.deleted', $p, [
                'title' => $p->title,
            ]);
        });
        ResearchPaper::updated(function (ResearchPaper $p) {
        // Chain status changes (e.g., NONE→UPLOADED→REGISTERED→CONFIRMED / FAILED)
        $origStatus = (string) $p->getOriginal('chain_status');
        $toStatus   = (string) $p->chain_status;

        if ($origStatus !== $toStatus) {
            Activity::log('paper.chain_status_changed', $p, [
                'from'         => $origStatus,
                'to'           => $toStatus,
                'tx_hash'      => (string) ($p->tx_hash ?? ''),
                'block_number' => (int) ($p->block_number ?? 0),
                'confirmed_at' => optional($p->confirmed_at)->toIso8601String(),
            ]);

            if ($toStatus === ResearchPaper::STATUS_REGISTERED) {
                Activity::log('paper.blockchain.registered', $p, [
                    'tx_hash' => (string) ($p->tx_hash ?? ''),
                ]);
            } elseif ($toStatus === ResearchPaper::STATUS_CONFIRMED) {
                Activity::log('paper.blockchain.confirmed', $p, [
                    'tx_hash'      => (string) ($p->tx_hash ?? ''),
                    'block_number' => (int) ($p->block_number ?? 0),
                    'confirmed_at' => optional($p->confirmed_at)->toIso8601String(),
                ]);
            } elseif ($toStatus === ResearchPaper::STATUS_FAILED) {
                Activity::log('paper.blockchain.failed', $p, [
                    'tx_hash' => (string) ($p->tx_hash ?? ''),
                ]);
            }
        }

        // First-time tx_hash set
        $origTx = (string) ($p->getOriginal('tx_hash') ?? '');
        if (!$origTx && $p->tx_hash) {
            Activity::log('paper.tx_set', $p, [
                'tx_hash' => (string) $p->tx_hash,
            ]);
        }
    });


    /* ---- USERS (Profile & Management) ---- */
User::created(function (User $u) {
    Activity::log('user.created', $u, [
        'name'  => $u->name,
        'email' => $u->email,
        'role'  => (string) $u->role,
        'active'=> (bool) $u->is_active,
    ]);
});

User::updated(function (User $u) {
    $orig = $u->getOriginal();

    // Track specific management-type changes
    if (($orig['role'] ?? null) !== $u->role) {
        Activity::log('user.role_changed', $u, [
            'from' => (string) ($orig['role'] ?? ''),
            'to'   => (string) $u->role,
        ]);
    }

    if ((bool)($orig['is_active'] ?? false) !== (bool)$u->is_active) {
        Activity::log('user.status_changed', $u, [
            'from' => (bool) ($orig['is_active'] ?? false),
            'to'   => (bool) $u->is_active,
        ]);
    }

    if (($orig['email'] ?? null) !== $u->email) {
        Activity::log('user.email_changed', $u, [
            'from' => (string) ($orig['email'] ?? ''),
            'to'   => (string) $u->email,
        ]);
    }

    if (($orig['avatar'] ?? null) !== $u->avatar) {
        Activity::log('user.avatar_updated', $u, [
            'from' => (string) ($orig['avatar'] ?? ''),
            'to'   => (string) ($u->avatar ?? ''),
        ]);
    }

    // If you hash with native cast, password diff check is safe (string compare)
    if (($orig['password'] ?? null) !== $u->password) {
        Activity::log('user.password_changed', $u, [
            'user_id' => (int) $u->id,
        ]);
    }

    // Generic catch-all (optional)
    Activity::log('user.updated', $u, [
        'updated_fields' => array_keys($u->getChanges()),
    ]);
});

User::deleted(function (User $u) {
    Activity::log('user.deleted', $u, [
        'name'  => $u->name,
        'email' => $u->email,
        'role'  => (string) $u->role,
    ]);
});



        /* ---- DOCUMENTS ---- */
        Document::created(function (Document $d) {
            Activity::log('document.created', $d, [
                'title_id' => $d->title_id,
                'chapter'  => $d->chapter,
                'format'   => $d->format,
            ]);
        });

        Document::updated(function (Document $d) {
    // Combined score (integer)
        $origScore = $d->getOriginal('plagiarism_score');
        if ($origScore != $d->plagiarism_score) {
            Activity::log('document.plagiarism_updated', $d, [
                'from' => (int) ($origScore ?? 0),
                'to'   => (int) ($d->plagiarism_score ?? 0),
            ]);
        }

        // Internal similarity (float)
        $origInternal = $d->getOriginal('plagiarism_internal');
        if ($origInternal != $d->plagiarism_internal) {
            Activity::log('document.plagiarism_internal_updated', $d, [
                'from' => (float) ($origInternal ?? 0),
                'to'   => (float) ($d->plagiarism_internal ?? 0),
            ]);
        }

        // External similarity (float)
        $origExternal = $d->getOriginal('plagiarism_external');
        if ($origExternal != $d->plagiarism_external) {
            Activity::log('document.plagiarism_external_updated', $d, [
                'from' => (float) ($origExternal ?? 0),
                'to'   => (float) ($d->plagiarism_external ?? 0),
            ]);
        }

        // File path attached/changed
        $origPath = (string) ($d->getOriginal('file_path') ?? '');
        $newPath  = (string) ($d->file_path ?? '');
        if ($origPath !== $newPath) {
            Activity::log('document.file_updated', $d, [
                'from' => $origPath,
                'to'   => $newPath,
            ]);
        }
    });


        /* ---- ADVISER REQUESTS ---- */
        AdviserRequest::created(function (AdviserRequest $r) {
            Activity::log('adviser_request.created', $r, [
                'title_id'   => $r->title_id,
                'adviser_id' => $r->adviser_id,
                'status'     => $r->status,
            ]);
        });

        AdviserRequest::updated(function (AdviserRequest $r) {
        $orig = (string) $r->getOriginal('status');
        $to   = (string) $r->status;

        if ($orig !== $to) {
            Activity::log('adviser_request.status_changed', $r, [
                'from'          => $orig,
                'to'            => $to,
                'requested_by'  => (string) ($r->requested_by ?? ''), // 'student' | 'adviser'
                'title_id'      => (int) $r->title_id,
                'adviser_id'    => (int) $r->adviser_id,
            ]);

            // Granular accept / reject
            if ($to === 'ACCEPTED') {
                Activity::log('adviser_request.accepted', $r, [
                    'requested_by' => (string) ($r->requested_by ?? ''),
                    'title_id'     => (int) $r->title_id,
                    'adviser_id'   => (int) $r->adviser_id,
                ]);
            } elseif (in_array($to, ['REJECTED','DECLINED'], true)) {
                Activity::log('adviser_request.rejected', $r, [
                    'requested_by' => (string) ($r->requested_by ?? ''),
                    'title_id'     => (int) $r->title_id,
                    'adviser_id'   => (int) $r->adviser_id,
                    'message'      => (string) ($r->message ?? ''),
                ]);
            }
        }
    });


    /* ---- BLOCKCHAIN REQUESTS ---- */
BlockchainRequest::created(function (BlockchainRequest $br) {
    Activity::log('blockchain.request.created', $br, [
        'paper_id'    => (int) $br->paper_id,
        'type'        => (string) $br->type,   // e.g., REGISTER, CONFIRM
        'status'      => (string) $br->status, // typically 'PENDING'
        'requester'   => $br->requester?->name,
    ]);
});

BlockchainRequest::updated(function (BlockchainRequest $br) {
    $orig = (string) $br->getOriginal('status');
    $to   = (string) $br->status;

    if ($orig !== $to) {
        Activity::log('blockchain.request.status_changed', $br, [
            'from'      => $orig,
            'to'        => $to,
            'paper_id'  => (int) $br->paper_id,
            'type'      => (string) $br->type,
            'acted_by'  => $br->actor?->name,
            'acted_at'  => optional($br->acted_at)->toIso8601String(),
            'reason'    => (string) ($br->reason ?? ''),
        ]);

        if ($to === 'APPROVED') {
            Activity::log('blockchain.request.approved', $br, [
                'paper_id' => (int) $br->paper_id,
                'type'     => (string) $br->type,
                'acted_by' => $br->actor?->name,
            ]);
        } elseif ($to === 'REJECTED') {
            Activity::log('blockchain.request.rejected', $br, [
                'paper_id' => (int) $br->paper_id,
                'type'     => (string) $br->type,
                'acted_by' => $br->actor?->name,
                'reason'   => (string) ($br->reason ?? ''),
            ]);
        }
    }
});


    }
}
