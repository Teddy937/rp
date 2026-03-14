<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cachedroles and permissions;
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ──────────────────────────────────────────────────
        $permissions = [
            // Branch & Store
            'Can view branches',
            'Can create branches',
            'Can update branches',
            'Can delete branches',
            'Can manage stores',
            'Can view branches',
            'Can view stores',

            // SKU
            'Can manage skus',
            'Can view skus',

            // Stock
            'Can view stoke ledger',
            'Can record sale',
            'Can request transfer',
            'Can verify transfer',
            'Can approve transfer',
            'Can manage adjustments',
            'Can manage procurement',

            // Users
            'Can manage users',
            'Can view users',

            // Reports & Audit
            'Can view audit logs',
            'Can view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        // ── Roles ─────────────────────────────────────────────────────────

        // Administrator — full access
        $admin = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'sanctum']);
        $admin->syncPermissions($permissions);

        // Branch Manager — branch-scoped operations
        $branchManager = Role::firstOrCreate(['name' => 'Branch Manager', 'guard_name' => 'sanctum']);
        $branchManager->syncPermissions([
            'Can view branches',
            'Can create branches',
            'Can update branches',
            'Can delete branches',
            'Can manage stores',
            'Can view stores',
            'Can manage skus',
            'Can view skus',
            'Can view stoke ledger',
            'Can record sale',
            'Can request transfer',
            'Can approve transfer',
            'Can manage adjustments',
            'Can view users',
            'Can view reports',
        ]);

        // Store Manager — store-scoped operations only
        $storeManager = Role::firstOrCreate(['name' => 'Store Manager', 'guard_name' => 'sanctum']);
        $storeManager->syncPermissions([
            'Can view stores',
            'Can view skus',
            'Can view stoke ledger',
            'Can record sale',
            'Can request transfer',
            'Can view reports',
        ]);

        $this->command->info('✅ Roles and permissions seeded.');
    }
}
