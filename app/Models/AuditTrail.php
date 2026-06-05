<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $fillable = [
        'employeeid',
        'actor_name',
        'event',
        'description',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'method',
        'url',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Central entry point for writing an audit record.
     * Automatically fills the actor and request context; anything passed
     * in $data overrides the defaults (e.g. actor for login/failed events).
     */
    public static function record(array $data): void
    {
        try {
            $user = auth()->user();
            $request = request();

            $defaults = [
                'employeeid' => $user->employeeid ?? null,
                'actor_name' => $user
                    ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                    : null,
                'method'     => $request?->method(),
                'url'        => $request?->fullUrl(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ];

            static::create(array_merge($defaults, $data));
        } catch (\Throwable $e) {
            // Auditing must never break the underlying action.
            \Illuminate\Support\Facades\Log::warning('Audit trail write failed: ' . $e->getMessage());
        }
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'employeeid', 'employeeid');
    }
}
