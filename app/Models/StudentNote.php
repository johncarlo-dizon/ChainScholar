<?php

// app/Models/StudentNote.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNote extends Model
{
    protected $fillable = [
        'title_id', 'document_id', 'adviser_id', 'student_id', 'content',
    ];

    public function adviser()  { return $this->belongsTo(\App\Models\User::class, 'adviser_id'); }
    public function student()  { return $this->belongsTo(\App\Models\User::class, 'student_id'); }
    public function document() { return $this->belongsTo(\App\Models\Document::class, 'document_id'); }
    public function title()    { return $this->belongsTo(\App\Models\Title::class, 'title_id'); }
}
