<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Store;
use Illuminate\Database\Seeder;

class BranchAndStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ── Branch A → 1 Store ───────────────────────────────────────────
        $branchA = Branch::create([
            'name'      => 'Branch A - Nairobi CBD',
            'code'      => 'BRA',
            'location'  => 'Nairobi CBD, Tom Mboya Street',
            'phone'     => '+254 20 000 0001',
            'is_active' => true,
        ]);

        Store::create([
            'branch_id' => $branchA->id,
            'name'      => 'Store A1 - CBD Main',
            'code'      => 'STA1',
            'location'  => 'Ground Floor, CBD',
            'phone'     => '+254 20 000 0011',
            'is_active' => true,
        ]);

        // ── Branch B → 2 Stores ──────────────────────────────────────────
        $branchB = Branch::create([
            'name'      => 'Branch B - Westlands',
            'code'      => 'BRB',
            'location'  => 'Westlands, Waiyaki Way',
            'phone'     => '+254 20 000 0002',
            'is_active' => true,
        ]);

        Store::create([
            'branch_id' => $branchB->id,
            'name'      => 'Store B1 - Westlands Main',
            'code'      => 'STB1',
            'location'  => 'Westlands Shopping Centre',
            'phone'     => '+254 20 000 0021',
            'is_active' => true,
        ]);

        Store::create([
            'branch_id' => $branchB->id,
            'name'      => 'Store B2 - Westlands Annex',
            'code'      => 'STB2',
            'location'  => 'Westlands, Mpaka Road',
            'phone'     => '+254 20 000 0022',
            'is_active' => true,
        ]);

        $this->command->info('✅ Branches and stores seeded: 2 branches, 3 stores.');
    }
}
