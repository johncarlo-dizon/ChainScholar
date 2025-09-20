<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

use App\Models\Notification;
use App\Models\Title;
use App\Models\Announcement;
use App\Models\ResearchPaper;
use App\Models\Document;
use App\Models\AdviserRequest;

use App\Services\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
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

        /* ---- DOCUMENTS ---- */
        Document::created(function (Document $d) {
            Activity::log('document.created', $d, [
                'title_id' => $d->title_id,
                'chapter'  => $d->chapter,
                'format'   => $d->format,
            ]);
        });

        Document::updated(function (Document $d) {
            $orig = $d->getOriginal('plagiarism_score');
            if ($orig != $d->plagiarism_score) {
                Activity::log('document.plagiarism_updated', $d, [
                    'from' => (int) ($orig ?? 0),
                    'to'   => (int) ($d->plagiarism_score ?? 0),
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
            $orig = $r->getOriginal('status');
            if ($orig !== $r->status) {
                Activity::log('adviser_request.status_changed', $r, [
                    'from' => (string) $orig,
                    'to'   => (string) $r->status,
                ]);
            }
        });
    }
}
