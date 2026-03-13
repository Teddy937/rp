<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Sku;
use App\Models\Stock_ledger;
use App\Models\Stock_movement;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $storeA1 = Store::where('code', 'STA1')->first();
        $storeB1 = Store::where('code', 'STB1')->first();
        $storeB2 = Store::where('code', 'STB2')->first();
        $branchA = Branch::where('code', 'BRA')->first();
        $branchB = Branch::where('code', 'BRB')->first();

        $admin = User::where('email', 'admin@kkwholesalers.co.ke')->first();
        $alice = User::where('email', 'alice@kkwholesalers.co.ke')->first();
        $brian = User::where('email', 'brian@kkwholesalers.co.ke')->first();
        $carol = User::where('email', 'carol@kkwholesalers.co.ke')->first();
        $david = User::where('email', 'david@kkwholesalers.co.ke')->first();
        $eve   = User::where('email', 'eve@kkwholesalers.co.ke')->first();

        $skus   = Sku::all();
        $stores = collect([$storeA1, $storeB1, $storeB2]);

        // ── Step 1: Seed stock ledger with opening stock ─────────────────
        // Each store gets healthy opening stock for all SKUs
        $openingStock = [
            'SKU-UNG-001' => 300,
            'SKU-OIL-001' => 200,
            'SKU-SUG-001' => 250,
            'SKU-SAL-001' => 400,
            'SKU-DET-001' => 150,
            'SKU-MAR-001' => 180,
            'SKU-ROY-001' => 350,
            'SKU-BEV-001' => 500,
            'SKU-DET-002' => 120,
            'SKU-NOO-001' => 600,
        ];

        foreach ($stores as $store) {
            foreach ($skus as $sku) {
                $qty = $openingStock[$sku->code] ?? 100;
                Stock_ledger::updateOrCreate(
                    ['store_id' => $store->id, 'sku_id' => $sku->id],
                    ['quantity' => $qty]
                );
            }
        }

        $this->command->info('✅ Opening stock seeded.');

        // ── Step 2: Procurement — 30 days ago, all stores stocked up ─────
        foreach ($stores as $store) {
            $branch    = $store->branch_id == $branchA->id ? $branchA : $branchB;
            $createdBy = $store->branch_id == $branchA->id ? $alice->id : $brian->id;

            foreach ($skus->random(6) as $sku) {
                $qty = rand(100, 300);
                $this->createMovement([
                    'type'        => Stock_movement::TYPE_PROCUREMENT,
                    'status'      => Stock_movement::STATUS_COMPLETED,
                    'sku_id'      => $sku->id,
                    'to_store_id' => $store->id,
                    'quantity'    => $qty,
                    'unit_cost'   => $sku->unit_cost,
                    'notes'       => 'Opening procurement',
                    'created_by'  => $createdBy,
                    'date'        => now()->subDays(rand(25, 30)),
                ]);
            }
        }

        $this->command->info('✅ Procurement movements seeded.');

        // ── Step 3: Sales — spread across last 30 days ───────────────────
        // Vary volume per day so the line chart looks natural
        $storeManagers = [
            $storeA1->id => $carol->id,
            $storeB1->id => $david->id,
            $storeB2->id => $eve->id,
        ];

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $date          = now()->subDays($daysAgo);
            $isWeekend     = in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
            $salesPerStore = $isWeekend ? rand(8, 15) : rand(3, 8);

            foreach ($stores as $store) {
                $managerId = $storeManagers[$store->id];
                $count     = rand(1, $salesPerStore);

                for ($i = 0; $i < $count; $i++) {
                    $sku = $skus->random();
                    $qty = rand(1, 20);

                    $this->createMovement([
                        'type'          => Stock_movement::TYPE_SALE,
                        'status'        => Stock_movement::STATUS_COMPLETED,
                        'sku_id'        => $sku->id,
                        'from_store_id' => $store->id,
                        'quantity'      => $qty,
                        'unit_cost'     => $sku->unit_cost,
                        'notes'         => 'Counter sale',
                        'created_by'    => $managerId,
                        'date'          => $date->copy()->addHours(rand(8, 18))->addMinutes(rand(0, 59)),
                    ]);
                }
            }
        }

        $this->command->info('✅ Sales movements seeded.');

        // ── Step 4: Transfers — between stores, some pending, some done ──
        $transfers = [
            // completed transfers
            ['from' => $storeA1, 'to' => $storeB1, 'daysAgo' => 20, 'status' => 'completed', 'approver' => $brian->id],
            ['from' => $storeB1, 'to' => $storeB2, 'daysAgo' => 15, 'status' => 'completed', 'approver' => $brian->id],
            ['from' => $storeB2, 'to' => $storeA1, 'daysAgo' => 10, 'status' => 'completed', 'approver' => $alice->id],
            ['from' => $storeA1, 'to' => $storeB2, 'daysAgo' => 7, 'status' => 'completed', 'approver' => $brian->id],
            ['from' => $storeB1, 'to' => $storeA1, 'daysAgo' => 4, 'status' => 'completed', 'approver' => $alice->id],
            // rejected transfer
            ['from' => $storeA1, 'to' => $storeB1, 'daysAgo' => 12, 'status' => 'rejected', 'approver' => $brian->id],
            // pending transfers (for the pending approvals chart)
            ['from' => $storeB1, 'to' => $storeA1, 'daysAgo' => 1, 'status' => 'pending', 'approver' => null],
            ['from' => $storeB2, 'to' => $storeB1, 'daysAgo' => 1, 'status' => 'pending', 'approver' => null],
            ['from' => $storeA1, 'to' => $storeB2, 'daysAgo' => 0, 'status' => 'pending', 'approver' => null],
        ];

        foreach ($transfers as $t) {
            $sku         = $skus->random();
            $qty         = rand(10, 50);
            $requestedBy = $storeManagers[$t['from']->id];

            // transfer_out
            $out = $this->createMovement([
                'type'             => Stock_movement::TYPE_TRANSFER_OUT,
                'status'           => $t['status'],
                'sku_id'           => $sku->id,
                'from_store_id'    => $t['from']->id,
                'quantity'         => $qty,
                'unit_cost'        => $sku->unit_cost,
                'notes'            => 'Inter-store transfer',
                'rejection_reason' => $t['status'] === 'rejected' ? 'Insufficient demand at destination' : null,
                'created_by'       => $requestedBy,
                'approved_by'      => $t['approver'],
                'approved_at'      => $t['approver'] ? now()->subDays($t['daysAgo'])->addHours(2) : null,
                'date'             => now()->subDays($t['daysAgo']),
            ]);

            // transfer_in (only if completed)
            if ($t['status'] === 'completed') {
                $this->createMovement([
                    'type'        => Stock_movement::TYPE_TRANSFER_IN,
                    'status'      => Stock_movement::STATUS_COMPLETED,
                    'sku_id'      => $sku->id,
                    'to_store_id' => $t['to']->id,
                    'quantity'    => $qty,
                    'unit_cost'   => $sku->unit_cost,
                    'notes'       => 'Inter-store transfer received',
                    'created_by'  => $requestedBy,
                    'approved_by' => $t['approver'],
                    'approved_at' => now()->subDays($t['daysAgo'])->addHours(2),
                    'date'        => now()->subDays($t['daysAgo'])->addHours(2),
                ]);
            }
        }

        $this->command->info('✅ Transfer movements seeded.');

        // ── Step 5: Adjustments — a few corrections per store ────────────
        $adjustments = [
            ['store' => $storeA1, 'type' => Stock_movement::TYPE_ADJUSTMENT_OUT, 'note' => 'Damaged goods — flood', 'daysAgo' => 18, 'by' => $carol->id],
            ['store' => $storeB1, 'type' => Stock_movement::TYPE_ADJUSTMENT_OUT, 'note' => 'Stock count discrepancy', 'daysAgo' => 14, 'by' => $david->id],
            ['store' => $storeB2, 'type' => Stock_movement::TYPE_ADJUSTMENT_IN, 'note' => 'Returned goods from customer', 'daysAgo' => 10, 'by' => $eve->id],
            ['store' => $storeA1, 'type' => Stock_movement::TYPE_ADJUSTMENT_IN, 'note' => 'Supplier short delivery correction', 'daysAgo' => 6, 'by' => $carol->id],
            ['store' => $storeB1, 'type' => Stock_movement::TYPE_ADJUSTMENT_OUT, 'note' => 'Expired products disposal', 'daysAgo' => 3, 'by' => $david->id],
        ];

        foreach ($adjustments as $adj) {
            $sku = $skus->random();
            $this->createMovement([
                'type'          => $adj['type'],
                'status'        => Stock_movement::STATUS_COMPLETED,
                'sku_id'        => $sku->id,
                'from_store_id' => str_contains($adj['type'], 'out') ? $adj['store']->id : null,
                'to_store_id'   => str_contains($adj['type'], 'in') ? $adj['store']->id : null,
                'quantity'      => rand(2, 15),
                'unit_cost'     => $sku->unit_cost,
                'notes'         => $adj['note'],
                'created_by'    => $adj['by'],
                'date'          => now()->subDays($adj['daysAgo']),
            ]);
        }

        $this->command->info('✅ Adjustment movements seeded.');

        // ── Step 6: Set a few SKUs to low stock for the alerts chart ─────
        $lowStockSkus = $skus->random(3);
        foreach ($stores->random(2) as $store) {
            foreach ($lowStockSkus as $sku) {
                Stock_ledger::where('store_id', $store->id)
                    ->where('sku_id', $sku->id)
                    ->update(['quantity' => rand(0, (int) ($sku->reorder_level * 0.4))]);
            }
        }

        $this->command->info('✅ Low stock situations created.');

        $this->command->info('');
        $this->command->info('🎉 Demo data seeded successfully!');
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Procurement movements', Stock_movement::where('type', 'procurement')->count()],
                ['Sale movements', Stock_movement::where('type', 'sale')->count()],
                ['Transfer movements', Stock_movement::whereIn('type', ['transfer_in', 'transfer_out'])->count()],
                ['Adjustment movements', Stock_movement::whereIn('type', ['adjustment_in', 'adjustment_out'])->count()],
                ['Pending transfers', Stock_movement::where('status', 'pending')->count()],
                ['Low stock SKUs', Stock_ledger::lowStock()->count()],
            ]
        );
    }

    private function createMovement(array $data): Stock_movement
    {
        $movement = Stock_movement::create([
            'reference_no'     => 'REF-' . strtoupper(Str::random(8)),
            'type'             => $data['type'],
            'status'           => $data['status'],
            'sku_id'           => $data['sku_id'],
            'from_store_id'    => $data['from_store_id'] ?? null,
            'to_store_id'      => $data['to_store_id'] ?? null,
            'quantity'         => $data['quantity'],
            'unit_cost'        => $data['unit_cost'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'created_by'       => $data['created_by'],
            'approved_by'      => $data['approved_by'] ?? null,
            'approved_at'      => $data['approved_at'] ?? null,
        ]);

        // Backdate the timestamps
        $movement->timestamps = false;
        $movement->created_at = $data['date'];
        $movement->updated_at = $data['date'];
        $movement->saveQuietly();

        return $movement;
    }
}
