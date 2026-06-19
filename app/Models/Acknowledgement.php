<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acknowledgement extends Model
{
    protected $fillable = [
        'reference_type',
        'reference_id',
        'employeeid',
        'note',
        'acknowledged_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'employeeid', 'employeeid');
    }
}
