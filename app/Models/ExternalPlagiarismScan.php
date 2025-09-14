<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// App/Models/ExternalPlagiarismScan.php
class ExternalPlagiarismScan extends Model
{
    protected $table = 'external_plagiarism_scans';
    protected $guarded = [];

    protected $casts = [
        'document_id'  => 'integer',
        'score'        => 'integer',
        'credits_used' => 'integer',
        'sandbox'      => 'boolean',  // ← NEW
        'raw_payload'  => 'array',    // ← NEW
    ];

    public function matches()
    {
        return $this->hasMany(ExternalPlagiarismMatch::class, 'scan_id_fk');
    }
}

