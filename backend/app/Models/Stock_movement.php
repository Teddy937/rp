<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock_movement extends Model
{
    use SoftDeletes;

    // Movement types
    const TYPE_SALE           = 'sale';
    const TYPE_TRANSFER_OUT   = 'transfer_out';
    const TYPE_TRANSFER_IN    = 'transfer_in';
    const TYPE_ADJUSTMENT_IN  = 'adjustment_in';
    const TYPE_ADJUSTMENT_OUT = 'adjustment_out';
    const TYPE_PROCUREMENT    = 'procurement';

    // Statuses
    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED  = 'rejected';

    protected $guarded = [];

    protected $casts = [
        'quantity'     => 'decimal:4',
        'unit_cost'    => 'decimal:2',
        'total_cost'   => 'decimal:2',
        'approved_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }

    public function fromStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function toStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isTransfer(): bool
    {
        return in_array($this->type, [self::TYPE_TRANSFER_IN, self::TYPE_TRANSFER_OUT]);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where(function ($q) use ($storeId) {
            $q->where('from_store_id', $storeId)
                ->orWhere('to_store_id', $storeId);
        });
    }
}
