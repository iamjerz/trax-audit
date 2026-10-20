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

            $merged = array_merge($defaults, $data);

            // While an admin is "viewing as" this account (see
            // ImpersonationController), tag every row written during the
            // session with who's actually driving — not just the
            // impersonate_start/stop events themselves — so the trail never
            // reads as if the target did it on their own. Both the
            // human-readable description (visible everywhere one already
            // is, including the Excel export) and a structured new_values
            // key (for anything that queries it later).
            $impersonatorId = session()->get('impersonator_id');
            if ($impersonatorId && ! in_array($merged['event'] ?? null, ['impersonate_start', 'impersonate_stop'], true)) {
                $impersonatorName = session()->get('impersonator_name', $impersonatorId);
                $merged['description'] = trim(($merged['description'] ?? '') . " (while {$impersonatorName} was viewing as this account)");
                $merged['new_values'] = array_merge($merged['new_values'] ?? [], [
                    'impersonator_employeeid' => $impersonatorId,
                ]);
            }

            static::create($merged);
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
