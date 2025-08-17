<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdviserNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_id',
        'document_id',
        'adviser_id',
        'student_id',
        'content',
    ];

    // Relationships
    public function title()
    {
        return $this->belongsTo(Title::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
