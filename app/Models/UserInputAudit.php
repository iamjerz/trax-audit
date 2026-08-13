<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class UserInputAudit extends Model
{
    use Auditable;

    protected $fillable = [
        'audit_id',
        'lda_id',
        'audit_date_1',
        'audit_sup_name',
        'auditors_name',
        'audit_date_2',
        'invoice_id',
        'carrier_name',
        'client_code',
        'exception_status',
        'exception_owner',
        'is_calibration',
        'created_by',
    ];

    protected $casts = [
        'is_calibration' => 'boolean',
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
