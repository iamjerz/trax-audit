<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coaching extends Model
{
    //
    protected $fillable = [
        'reference',
        'smart',
        'grow',
        'created_by'
    ];

    protected $casts = [
        'smart' => 'array',
        'grow' => 'array',
    ];
}
