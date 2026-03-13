<?php
namespace App\Observers;

use App\Models\Stock_movement;
use App\Services\StockLedgerService;
use Illuminate\Support\Facades\Log;

class StockMovementObserver
{
    public function __construct(
        private readonly StockLedgerService $ledgerService
    ) {}
    /**
     * Handle the Stock_movement "created" event.
     */
    public function created(Stock_movement $movement): void
    {
        if ($movement->status === Stock_movement::STATUS_COMPLETED) {
            $this->syncLedger($movement);
        }
    }

    /**
     * Handle the Stock_movement "updated" event.
     */
    public function updated(Stock_movement $movement): void
    {
        $statusChanged = $movement->wasChanged('status');
        $nowCompleted  = $movement->status === Stock_movement::STATUS_COMPLETED;

        if ($statusChanged && $nowCompleted) {
            $this->syncLedger($movement);
        }
    }

    /**
     * Handle the Stock_movement "deleted" event.
     */
    public function deleted(Stock_movement $stock_movement): void
    {
        //
    }

    /**
     * Handle the Stock_movement "restored" event.
     */
    public function restored(Stock_movement $stock_movement): void
    {
        //
    }

    /**
     * Handle the Stock_movement "force deleted" event.
     */
    public function forceDeleted(Stock_movement $stock_movement): void
    {
        //
    }

    /**
     * Delegate ledger update to StockLedgerService based on movement type.
     */
    private function syncLedger(Stock_movement $movement): void
    {
        try {
            $this->ledgerService->applyMovement($movement);
        } catch (\Throwable $e) {
            Log::error('StockMovementObserver: ledger sync failed', [
                'movement_id'  => $movement->id,
                'reference_no' => $movement->reference_no,
                'error'        => $e->getMessage(),
            ]);

            throw $e; // re-throw to roll back the DB transaction
        }
    }
}
