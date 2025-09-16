<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdviserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'department',
        'field_of_expertise',
        'highest_degree',
        'degree_school',
        'degree_year',
        'advisory_years',
        'projects_handled',
        'notes',
    ];

    /* Relationships */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(AdviserAchievement::class);
    }

    public function researchInterests(): BelongsToMany
    {
        return $this->belongsToMany(
            ResearchInterest::class,
            'adviser_profile_research_interest'
        );
    }
}
