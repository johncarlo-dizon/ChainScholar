<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Activity
{
    /**
     * @param  string $action
     * @param  mixed|null $subject
     * @param  array $meta
     * @param  \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User|null $actor
     */
    public static function log(
        string $action,
        $subject = null,
        array $meta = [],
        AuthenticatableContract|User|null $actor = null
    ): ActivityLog {
        // Prefer explicit $actor; else try current auth user (may be null during logout)
        $actor = $actor ?: auth()->user();

        // Coerce Authenticatable → App\Models\User (so we can safely read id/role)
        $actorModel = match (true) {
            $actor instanceof User => $actor,
            $actor instanceof AuthenticatableContract => User::query()->find($actor->getAuthIdentifier()),
            default => null,
        };

        return ActivityLog::create([
            'user_id'      => $actorModel?->id,
            'role'         => $actorModel?->role,           // may be null if not resolvable
            'action'       => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'meta'         => $meta ?: null,
            'ip'           => Request::ip(),
            'user_agent'   => substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
