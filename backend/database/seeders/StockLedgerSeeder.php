<?php
namespace Database\Seeders;

use App\Models\Sku;
use App\Models\Stock_ledger;
use App\Models\Store;
use Illuminate\Database\Seeder;

class StockLedgerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $stores = Store::all();
        $skus   = Sku::all();

        if ($stores->isEmpty() || $skus->isEmpty()) {
            $this->command->warn('No stores or SKUs found. Run BranchAndStoreSeeder and SkuSeeder first.');
            return;
        }

        foreach ($stores as $store) {
            foreach ($skus as $sku) {
                Stock_ledger::updateOrCreate(
                    ['store_id' => $store->id, 'sku_id' => $sku->id],
                    ['quantity' => fake()->numberBetween(0, 200)]
                );
            }
        }

        $this->command->info('Stock ledger seeded: ' . ($stores->count() * $skus->count()) . ' rows.');
    }
}
