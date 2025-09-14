<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalPlagiarismMatch extends Model
{
    protected $table = 'external_plagiarism_matches';
    protected $guarded = []; // allow mass create/update

    protected $casts = [
        'scan_id_fk'  => 'integer',
        'document_id' => 'integer',
        'percent'     => 'integer',
    ];

    public function scan()
    {
        return $this->belongsTo(ExternalPlagiarismScan::class, 'scan_id_fk');
    }
}
