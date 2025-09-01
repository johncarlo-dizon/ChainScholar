<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'event_date',
        'user_id',
    ];

       protected $casts = [
        'event_date' => 'date', // or 'datetime' if it has time
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
