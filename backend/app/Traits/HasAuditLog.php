<?php
namespace App\Traits;

use App\Models\Audit_log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasAuditLog
{
    /**
     * Boot the trait. Hooks into model events automatically.
     */
    public static function bootHasAuditLog(): void
    {
        static::created(function ($model) {
            $model->recordAudit('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $model->recordAudit(
                'updated',
                $model->getOriginal(),
                $model->getChanges()
            );
        });

        static::deleted(function ($model) {
            $model->recordAudit('deleted', $model->getAttributes(), null);
        });
    }

    /**
     * Write an audit log entry.
     */
    public function recordAudit(
        string $action,
        ?array $oldValues,
        ?array $newValues,
        ?string $description = null
    ): void {
        // Strip sensitive fields
        $sensitive = ['password', 'remember_token'];
        $oldValues = $oldValues ? array_diff_key($oldValues, array_flip($sensitive)) : null;
        $newValues = $newValues ? array_diff_key($newValues, array_flip($sensitive)) : null;

        Audit_log::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'model_type'  => get_class($this),
            'model_id'    => $this->getKey(),
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
            'description' => $description,
        ]);
    }
}
