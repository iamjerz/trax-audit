<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TriadItems extends Model
{
    //
    protected $fillable = [
        'reference',
        'triad',
        'created_by',
        'reference_id' // 👈 add this
    ];

    protected $casts = [
        'triad' => 'array',
    ];

    public function user_info()
    {
        return $this->belongsTo(User::class, 'created_by', 'employeeid');
    }
}
