<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock_ledger extends Model
{

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeLowStock($query)
    {
        // Explicitly use the model's table name — avoids Laravel pluralizing to 'stock_ledgers'
        return $query->whereRaw('`stock_ledgers`.`quantity` <= (select `reorder_level` from `skus` where `skus`.`id` = `stock_ledgers`.`sku_id` and `skus`.`deleted_at` is null)');
    }
}
