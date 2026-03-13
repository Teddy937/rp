<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchA = Branch::where('code', 'BRA')->first();
        $branchB = Branch::where('code', 'BRB')->first();
        $storeA1 = Store::where('code', 'STA1')->first();
        $storeB1 = Store::where('code', 'STB1')->first();
        $storeB2 = Store::where('code', 'STB2')->first();

        // ── Administrator ────────────────────────────────────────────────
        $admin = User::create([
            'name'           => 'System Administrator',
            'email'          => 'admin@kkwholesalers.co.ke',
            'password'       => 'password',
            'account_status' => 'active',
        ]);
        $admin->assignRole('Administrator');

        $admin = User::create([
            'name'           => 'Thadeus Odenyo',
            'email'          => 'odenyothadeus@gmail.com',
            'password'       => 'password',
            'account_status' => 'active',
        ]);
        $admin->assignRole('Administrator');

        // jim.muguna@valuechainfactory.com

        $admin = User::create([
            'name'           => 'Jim Muguna',
            'email'          => 'jim.muguna@valuechainfactory.com',
            'password'       => 'password',
            'account_status' => 'active',
        ]);
        $admin->assignRole('Administrator');

        // ── Branch A Manager ─────────────────────────────────────────────
        $bmA = User::create([
            'name'           => 'Alice Wanjiku',
            'email'          => 'alice@kkwholesalers.co.ke',
            'password'       => 'password',
            'branch_id'      => $branchA->id,
            'account_status' => 'active',
        ]);
        $bmA->assignRole('Branch Manager');

        // ── Branch B Manager ─────────────────────────────────────────────
        $bmB = User::create([
            'name'           => 'Brian Otieno',
            'email'          => 'brian@kkwholesalers.co.ke',
            'password'       => 'password',
            'branch_id'      => $branchB->id,
            'account_status' => 'active',
        ]);
        $bmB->assignRole('Branch Manager');

        // ── Store A1 Manager ─────────────────────────────────────────────
        $smA1 = User::create([
            'name'           => 'Carol Muthoni',
            'email'          => 'carol@kkwholesalers.co.ke',
            'password'       => 'password',
            'branch_id'      => $branchA->id,
            'store_id'       => $storeA1->id,
            'account_status' => 'active',
        ]);
        $smA1->assignRole('Store Manager');

        // ── Store B1 Manager ─────────────────────────────────────────────
        $smB1 = User::create([
            'name'           => 'David Kamau',
            'email'          => 'david@kkwholesalers.co.ke',
            'password'       => 'password',
            'branch_id'      => $branchB->id,
            'store_id'       => $storeB1->id,
            'account_status' => 'active',
        ]);
        $smB1->assignRole('Store Manager');

        // ── Store B2 Manager ─────────────────────────────────────────────
        $smB2 = User::create([
            'name'           => 'Eve Nyambura',
            'email'          => 'eve@kkwholesalers.co.ke',
            'password'       => 'password',
            'branch_id'      => $branchB->id,
            'store_id'       => $storeB2->id,
            'account_status' => 'active',
        ]);
        $smB2->assignRole('Store Manager');

        $this->command->info('✅ Users seeded:');
        $this->command->table(
            ['Name', 'Email', 'Role', 'Password'],
            [
                ['System Administrator', 'admin@kkwholesalers.co.ke', 'Administrator', 'password'],
                ['Alice Wanjiku', 'alice@kkwholesalers.co.ke', 'Branch Manager', 'password'],
                ['Brian Otieno', 'brian@kkwholesalers.co.ke', 'Branch Manager', 'password'],
                ['Carol Muthoni', 'carol@kkwholesalers.co.ke', 'Store Manager', 'password'],
                ['David Kamau', 'david@kkwholesalers.co.ke', 'Store Manager', 'password'],
                ['Eve Nyambura', 'eve@kkwholesalers.co.ke', 'Store Manager', 'password'],
            ]
        );
    }
}
