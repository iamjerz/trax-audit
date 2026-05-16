<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInputAudit extends Model
{
    protected $fillable = [
        'audit_id',
        'lda_id',
        'audit_date_1',
        'audit_sup_name',
        'auditors_name',
        'audit_date_2',
        'invoice_id',
        'carrier_name',
        'exception_status',
        'exception_owner',
        'created_by',
    ];

    public function verification()
    {
        return $this->hasOne(Verification::class, 'audit_id', 'audit_id');
    }

    public function processCompliance()
    {
        return $this->hasOne(ProcessCompliance::class, 'audit_id', 'audit_id');
    }

    public function engagement()
    {
        return $this->hasOne(Engagement::class, 'audit_id', 'audit_id');
    }

    public function businessAnalytic()
    {
        return $this->hasOne(BusinessAnalytic::class, 'audit_id', 'audit_id');
    }

    public function ldaUser()
    {
        return $this->belongsTo(User::class, 'lda_id', 'employeeid');
    }
    public function auditSupervisor()
    {
        return $this->belongsTo(User::class, 'audit_sup_name', 'employeeid');
    }

}
