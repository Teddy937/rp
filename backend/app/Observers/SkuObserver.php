<?php
namespace App\Observers;

use App\Models\Sku;
use Illuminate\Support\Facades\Log;

class SkuObserver
{
    /**
     * Handle the Sku "created" event.
     */
    public function creating(Sku $sku): void
    {
        // Auto-generate code if not provided
        if (empty($sku->code)) {
            $sku->code = generate_sku_code($sku->name);
        }
    }

    /**
     * Handle the Sku "created" event.
     */
    public function created(Sku $sku): void
    {
        //
    }

    /**
     * Handle the Sku "updated" event.
     */
    public function updated(Sku $sku): void
    {
        //
    }

    /**
     * Handle the Sku "deleted" event.
     */
    public function deleted(Sku $sku): void
    {
        Log::info("SKU soft-deleted: [{$sku->code}] {$sku->name}");
    }

    /**
     * Handle the Sku "restored" event.
     */
    public function restored(Sku $sku): void
    {
        //
    }

    /**
     * Handle the Sku "force deleted" event.
     */
    public function forceDeleted(Sku $sku): void
    {
        //
    }
}
