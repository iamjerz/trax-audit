<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engagement extends Model
{
    protected $fillable = [
        'audit_id',
        'eng_comment_1',
        'eng_outcome_1',
        'eng_comment_2',
        'eng_outcome_2',
        'eng_comment_3',
        'eng_outcome_3',
        'eng_comment_4',
        'eng_outcome_4',
        'total_score',
        'created_by',
    ];

    public function audit()
    {
        return $this->belongsTo(UserInputAudit::class, 'audit_id', 'audit_id');
    }
}
