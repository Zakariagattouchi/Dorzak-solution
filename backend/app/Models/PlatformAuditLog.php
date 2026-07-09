<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per privileged platform action (append-only). Written via
 * PlatformAuditLog::record() from the platform controllers.
 */
class PlatformAuditLog extends Model
{
    public const UPDATED_AT = null; // append-only

    protected $fillable = [
        'admin_user_id', 'action', 'target_type', 'target_id', 'target_label', 'meta', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /**
     * Record a platform action. Never throws into the caller — auditing must not
     * break the operation it describes.
     */
    public static function record(
        ?User $admin,
        string $action,
        ?Model $target = null,
        ?string $targetLabel = null,
        array $meta = [],
        ?string $ip = null,
    ): void {
        try {
            static::create([
                'admin_user_id' => $admin?->id,
                'action' => $action,
                'target_type' => $target === null ? null : strtolower(class_basename($target)),
                'target_id' => $target?->getKey(),
                'target_label' => $targetLabel,
                'meta' => $meta ?: null,
                'ip_address' => $ip,
            ]);
        } catch (\Throwable) {
            // Swallow — a failed audit write must not abort the admin action.
        }
    }
}
