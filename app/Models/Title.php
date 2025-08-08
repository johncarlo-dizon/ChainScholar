<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Title extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'authors',
        'abstract',
        'keywords',
        'category',
        'sub_category',
        'research_type',
        'ethics_clearance_no',
        'review_comments',
        'submitted_at',
        'approved_at',
        'returned_at',
        'finaldocument_id',
        'status', // ✅ add this line
    ];
    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'returned_at' => 'datetime',
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function finalDocument()
    {
        return $this->belongsTo(Document::class, 'finaldocument_id');
    }
    
}
