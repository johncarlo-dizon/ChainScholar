<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchPaper extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'year',
        'authors',
        'filename',
        'department',
        'program',
        'abstract',
        'plagiarism_score', // <— added
        'extracted_text',
        'text_hash',
        'file_path',
        'file_disk',
        'sha256',
        'wallet',
        'tx_hash',
        'chain_id',
        'chain_status',
        'block_number',
        'confirmed_at',
    ];



    protected $casts = [
        'confirmed_at'      => 'datetime',
        'block_number'      => 'integer',
        'chain_id'          => 'integer',
        'plagiarism_score'  => 'integer', // <— added
    ];


    // Status helpers
    public const STATUS_NONE       = 'NONE';
    public const STATUS_UPLOADED   = 'UPLOADED';
    public const STATUS_REGISTERED = 'REGISTERED';
    public const STATUS_CONFIRMED  = 'CONFIRMED';
    public const STATUS_FAILED     = 'FAILED';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function getViewUrlAttribute(): string
    {
        return route('papers.view', $this);
    }
    public function getDownloadUrlAttribute(): string
    {
        return route('papers.download', $this);
    }


    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->chain_status) {
            self::STATUS_CONFIRMED  => 'bg-emerald-100 text-emerald-700',
            self::STATUS_REGISTERED => 'bg-amber-100 text-amber-700',
            self::STATUS_FAILED     => 'bg-rose-100 text-rose-700',
            self::STATUS_UPLOADED   => 'bg-sky-100 text-sky-700',
            default                 => 'bg-gray-100 text-gray-700',
        };
    }

    public function requests()
    {
        return $this->hasMany(\App\Models\BlockchainRequest::class, 'paper_id');
    }

    public function pendingRequest()
    {
        return $this->hasOne(\App\Models\BlockchainRequest::class, 'paper_id')
            ->where('status', 'PENDING')
            ->latestOfMany();
    }


    public function lastRequest()
    {
        return $this->hasOne(\App\Models\BlockchainRequest::class, 'paper_id')->latestOfMany();
    }

}
