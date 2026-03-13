<?php
namespace App\Services;

use App\Models\Stock_movement;
use App\Repositories\StockLedgerRepository;
use Illuminate\Support\Facades\DB;

class StockLedgerService
{
    public function __construct(
        private readonly StockLedgerRepository $ledgerRepo
    ) {}

    /**
     * Apply a completed movement to the stock ledger.
     * Called by StockMovementObserver.
     */
    public function applyMovement(Stock_movement $movement): void
    {
        DB::transaction(function () use ($movement) {
            match ($movement->type) {
                Stock_movement::TYPE_SALE           => $this->deductStock($movement->from_store_id, $movement->sku_id, $movement->quantity),
                Stock_movement::TYPE_TRANSFER_OUT   => $this->deductStock($movement->from_store_id, $movement->sku_id, $movement->quantity),
                Stock_movement::TYPE_TRANSFER_IN    => $this->addStock($movement->to_store_id, $movement->sku_id, $movement->quantity),
                Stock_movement::TYPE_ADJUSTMENT_IN  => $this->addStock($movement->to_store_id, $movement->sku_id, $movement->quantity),
                Stock_movement::TYPE_ADJUSTMENT_OUT => $this->deductStock($movement->from_store_id, $movement->sku_id, $movement->quantity),
                Stock_movement::TYPE_PROCUREMENT    => $this->addStock($movement->to_store_id, $movement->sku_id, $movement->quantity),
                default                             => throw new \InvalidArgumentException("Unknown movement type: {$movement->type}"),
            };
        });
    }

    private function addStock(int $storeId, int $skuId, float $quantity): void
    {
        $this->ledgerRepo->upsertStock($storeId, $skuId, $quantity);
    }

    private function deductStock(int $storeId, int $skuId, float $quantity): void
    {
        $ledger = $this->ledgerRepo->getStockForStoreSku($storeId, $skuId, lock: true);

        if (! $ledger || $ledger->quantity < $quantity) {
            throw new \DomainException(
                "Insufficient stock. Available: " . ($ledger?->quantity ?? 0) . ", Requested: {$quantity}"
            );
        }

        $this->ledgerRepo->upsertStock($storeId, $skuId, -$quantity);
    }
}
