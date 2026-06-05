<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class ProcessCompliance extends Model
{
    use Auditable;

    protected $fillable = [
        'audit_id',
        'pc_comment_1',
        'pc_outcome_1',
        'pc_comment_2',
        'pc_outcome_2',
        'pc_comment_3',
        'pc_outcome_3',
        'pc_comment_4',
        'pc_outcome_4',
        'total_score',
        'created_by',
    ];

    public function audit()
    {
        return $this->belongsTo(UserInputAudit::class, 'audit_id', 'audit_id');
    }
}
