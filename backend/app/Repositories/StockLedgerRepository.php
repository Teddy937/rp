<?php
namespace App\Repositories;

use App\Models\Stock_ledger;
use App\Repositories\Contracts\StockLedgerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StockLedgerRepository extends BaseRepository implements StockLedgerRepositoryInterface
{
    public function __construct(Stock_ledger $model)
    {
        parent::__construct($model);
    }

    public function getStoreStock(int $storeId, array $filters = []): Collection
    {
        $query = $this->query()
            ->with(['sku', 'store.branch'])
            ->where('store_id', $storeId);

        if (! empty($filters['sku_id'])) {
            $query->where('sku_id', $filters['sku_id']);
        }

        if (! empty($filters['search'])) {
            $query->whereHas('sku', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('code', 'like', "%{$filters['search']}%");
            });
        }

        return $query->get();
    }

    /**
     * Get a specific ledger row with a pessimistic lock (for transactions).
     * Prevents race conditions during concurrent sales.
     */
    public function getStockForStoreSku(int $storeId, int $skuId, bool $lock = false): ?Stock_ledger
    {
        $query = $this->query()
            ->where('store_id', $storeId)
            ->where('sku_id', $skuId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function getLowStockItems(int $storeId): Collection
    {
        return $this->query()
            ->with('sku')
            ->where('store_id', $storeId)
            ->lowStock()
            ->get();
    }

    /**
     * Upsert a ledger row (create if not exists, otherwise update quantity).
     */
    public function upsertStock(int $storeId, int $skuId, float $quantityDelta): Stock_ledger
    {
        $ledger = Stock_ledger::firstOrCreate(
            ['store_id' => $storeId, 'sku_id' => $skuId],
            ['quantity' => 0]
        );

        $ledger->increment('quantity', $quantityDelta);
        return $ledger->fresh();
    }
}
