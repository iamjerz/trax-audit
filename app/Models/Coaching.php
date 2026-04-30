<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Coaching extends Model
{
    //
    protected $fillable = [
        'reference',
        'reference_type',
        'reference_id',
        'smart',
        'grow',
        'created_by'
    ];

    protected $casts = [
        'smart' => 'array',
        'grow' => 'array',
    ];


    public function user_info()
    {
        return $this->belongsTo(User::class, 'created_by', 'employeeid');
    }
}
