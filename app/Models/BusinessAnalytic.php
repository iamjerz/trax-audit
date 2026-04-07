<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessAnalytic extends Model
{

    
    protected $fillable = [
        'audit_id',
        'sign_carrier',
        'follow_up',
        'many_days',
        'cause_issue',
        'impact_area',
        'impact_factor',
        'accountable_factors',
        'root_cause',
        'created_by',
    ];

    public function audit()
    {
        return $this->belongsTo(UserInputAudit::class, 'audit_id', 'audit_id');
    }
}
