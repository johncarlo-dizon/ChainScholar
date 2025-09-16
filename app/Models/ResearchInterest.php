<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ResearchInterest extends Model
{
    protected $fillable = ['name', 'slug'];

    public function adviserProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            AdviserProfile::class,
            'adviser_profile_research_interest'
        );
    }

    // Optional: keep slug synced when setting name
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }
}
