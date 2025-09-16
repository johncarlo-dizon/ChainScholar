<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdviserAchievement extends Model
{
    protected $fillable = [
        'adviser_profile_id',
        'title',
        'issuer',
        'year',
        'description',
    ];

    public function adviserProfile(): BelongsTo
    {
        return $this->belongsTo(AdviserProfile::class);
    }
}
