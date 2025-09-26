<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockchainRequest extends Model
{
    protected $fillable = [
        'paper_id', 'requester_id', 'type', 'status', 'reason', 'acted_by', 'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(ResearchPaper::class, 'paper_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
