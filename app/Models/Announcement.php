<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    /* ---------- Enums ---------- */
    public const TIER_URGENT    = 'URGENT';
    public const TIER_IMPORTANT = 'IMPORTANT';
    public const TIER_GENERAL   = 'GENERAL';

    public const TIERS = [
        self::TIER_URGENT,
        self::TIER_IMPORTANT,
        self::TIER_GENERAL,
    ];

    public const AUD_ALL     = 'ALL';
    public const AUD_STUDENT = 'STUDENT';
    public const AUD_ADVISER = 'ADVISER';
    public const AUD_ADMIN   = 'ADMIN';

    public const AUDIENCES = [
        self::AUD_ALL,
        self::AUD_STUDENT,
        self::AUD_ADVISER,
        self::AUD_ADMIN,
    ];

    protected $fillable = [
        'title',
        'body',
        'event_date',
        'user_id',
        'tier',
        'audience',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* ---------- UI helpers ---------- */

    public function getTierLabelAttribute(): string
    {
        return match ($this->tier) {
            self::TIER_URGENT    => 'Urgent',
            self::TIER_IMPORTANT => 'Important',
            default              => 'General',
        };
    }

    public function getTierBadgeClassesAttribute(): string
    {
        return match ($this->tier) {
            self::TIER_URGENT    => 'bg-red-100 text-red-700 ring-1 ring-red-200',
            self::TIER_IMPORTANT => 'bg-amber-100 text-amber-800 ring-1 ring-amber-200',
            default              => 'bg-blue-100 text-blue-700 ring-1 ring-blue-200',
        };
    }

    public function getAudienceLabelAttribute(): string
    {
        return match ($this->audience) {
            self::AUD_STUDENT => 'Students',
            self::AUD_ADVISER => 'Advisers',
            self::AUD_ADMIN   => 'Admins',
            default           => 'Everyone',
        };
    }

    public function getAudienceBadgeClassesAttribute(): string
    {
        return match ($this->audience) {
            self::AUD_STUDENT => 'bg-green-100 text-green-800 ring-1 ring-green-200',
            self::AUD_ADVISER => 'bg-purple-100 text-purple-800 ring-1 ring-purple-200',
            self::AUD_ADMIN   => 'bg-gray-200 text-gray-800 ring-1 ring-gray-300',
            default           => 'bg-sky-100 text-sky-800 ring-1 ring-sky-200',
        };
    }
}
