<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Title extends Model
{
    protected $fillable = [
        'owner_id',
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
        'verified_at',
        'adviser_assigned_at',
        'primary_adviser_id',
        'final_document_id',
        'status',
    ];
    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'returned_at' => 'datetime',
        'verified_at' => 'datetime',
        'adviser_assigned_at' => 'datetime',
    ];
    

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function primaryAdviser()
    {
        return $this->belongsTo(User::class, 'primary_adviser_id');
    }


    public function finalDocument()
    {
        return $this->belongsTo(Document::class, 'final_document_id');
    }

    public function adviserRequests()
    {
        return $this->hasMany(AdviserRequest::class);
    }
    public function adviserNotes()
    {
        return $this->hasMany(\App\Models\AdviserNote::class);
    }

}
