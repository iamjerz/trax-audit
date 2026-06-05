<?php

namespace App\Traits;

use App\Models\AuditTrail;

/**
 * Add `use Auditable;` to any Eloquent model to automatically log
 * create / update / delete actions (with before/after diffs) to the
 * audit_trails table.
 */
trait Auditable
{
    /**
     * Attributes that should never be written to the audit log.
     */
    protected array $auditExclude = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditTrail::record([
                'event'          => 'created',
                'description'    => 'Created ' . class_basename($model) . ' #' . $model->getKey(),
                'auditable_type' => class_basename($model),
                'auditable_id'   => $model->getKey(),
                'new_values'     => $model->auditScrub($model->getAttributes()),
            ]);
        });

        static::updated(function ($model) {
            $changes = $model->auditScrub($model->getChanges());

            // Nothing meaningful changed (e.g. only timestamps) — skip.
            if (empty($changes)) {
                return;
            }

            $old = [];
            foreach (array_keys($changes) as $key) {
                $old[$key] = $model->getOriginal($key);
            }

            AuditTrail::record([
                'event'          => 'updated',
                'description'    => 'Updated ' . class_basename($model) . ' #' . $model->getKey(),
                'auditable_type' => class_basename($model),
                'auditable_id'   => $model->getKey(),
                'old_values'     => $old,
                'new_values'     => $changes,
            ]);
        });

        static::deleted(function ($model) {
            AuditTrail::record([
                'event'          => 'deleted',
                'description'    => 'Deleted ' . class_basename($model) . ' #' . $model->getKey(),
                'auditable_type' => class_basename($model),
                'auditable_id'   => $model->getKey(),
                'old_values'     => $model->auditScrub($model->getOriginal()),
            ]);
        });
    }

    /**
     * Remove sensitive / noisy keys from an attribute set before logging.
     */
    public function auditScrub(array $attributes): array
    {
        return collect($attributes)
            ->except($this->auditExclude)
            ->all();
    }
}
