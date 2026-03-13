<?php
namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sku extends Model
{
    use SoftDeletes, HasAuditLog;

    protected $guarded = [];

    protected $casts = [
        'is_active'     => 'boolean',
        'unit_cost'     => 'decimal:2',
        'unit_price'    => 'decimal:2',
        'reorder_level' => 'integer',
        'metadata'      => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function stockLedger(): HasMany
    {
        return $this->hasMany(Stock_ledger::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Stock_movement::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%");
        });
    }
}
