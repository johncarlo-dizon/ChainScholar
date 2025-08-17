<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdviserRequest extends Model
{
    protected $fillable = [
        'title_id',
        'adviser_id',
        'requested_by',
        'status',
        'message',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    /** Relationships */
    public function title()
    {
        return $this->belongsTo(Title::class);
    }

    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }
}
