<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriadItems extends Model
{
    //
    protected $fillable = [
        'reference',
        'triad',
        'created_by'
    ];

    protected $casts = [
        'triad' => 'array',
    ];
}
