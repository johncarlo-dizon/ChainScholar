<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ResearchPaper extends Model
{
    //
   protected $fillable = [
        'user_id',
        'title',
        'year',
        'authors',
        'filename',
        'department',
        'program',
        'abstract',
        'extracted_text',
        'file_path'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
