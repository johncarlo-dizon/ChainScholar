<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    //
      protected $fillable = [
        'user_id',
        'title',
        'name',
        'content',
        'file_path'
    ];
    // app/Models/Document.php
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
