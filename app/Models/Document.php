<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'title_id',
        'chapter',
        'content',
        'file_path',
        'plagiarism_internal',   // NEW
        'plagiarism_external', 
        'plagiarism_score',
        'format',
    ];


    protected $casts = [
        'plagiarism_internal' => 'float',
        'plagiarism_external' => 'float',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function title()
    {
        return $this->belongsTo(Title::class);
    }
    public function titleRelation()
    {
        // alias to the real relationship
        return $this->belongsTo(Title::class, 'title_id')->withDefault();
    }
    public function adviserNotes()
    {
        return $this->hasMany(\App\Models\AdviserNote::class);
    }
}
