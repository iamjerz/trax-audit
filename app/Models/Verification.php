<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    protected $fillable = [
        'audit_id',
        'ver_comment_1',
        'ver_outcome_1',
        'ver_comment_2',
        'ver_outcome_2',
        'total_score',
        'created_by',
    ];

    public function audit()
    {
        return $this->belongsTo(UserInputAudit::class, 'audit_id', 'audit_id');
    }
}
