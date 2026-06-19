<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreCorrection extends Model
{
    protected $fillable = [
        'audit_id',
        'dispute_id',
        'changed_by',
        'reason',
        'old_values',
        'new_values',
        'status',
        'approved_by',
        'approved_at',
        'decision_note',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'approved_at' => 'datetime',
    ];

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by', 'employeeid');
    }
}
