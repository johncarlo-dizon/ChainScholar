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
        'format',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function titleRelation()
    {
        return $this->belongsTo(Title::class, 'title_id');
    }
}
