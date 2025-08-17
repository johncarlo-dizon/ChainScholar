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
        'plagiarism_score',
        'format',
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
