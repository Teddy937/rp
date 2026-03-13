<?php
namespace Database\Seeders;

use App\Models\Sku;
use Illuminate\Database\Seeder;

class SkuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skus = [
            [
                'name'          => 'Unga Pembe 2kg',
                'code'          => 'SKU-UNG-001',
                'unit'          => 'bag',
                'description'   => 'Pembe Maize Flour 2kg bag',
                'unit_cost'     => 95.00,
                'unit_price'    => 120.00,
                'reorder_level' => 50,
            ],
            [
                'name'          => 'Cooking Oil 1L',
                'code'          => 'SKU-OIL-001',
                'unit'          => 'bottle',
                'description'   => 'Vegetable cooking oil 1 litre bottle',
                'unit_cost'     => 180.00,
                'unit_price'    => 220.00,
                'reorder_level' => 30,
            ],
            [
                'name'          => 'Sugar 1kg',
                'code'          => 'SKU-SUG-001',
                'unit'          => 'packet',
                'description'   => 'White granulated sugar 1kg',
                'unit_cost'     => 110.00,
                'unit_price'    => 140.00,
                'reorder_level' => 40,
            ],
            [
                'name'          => 'Saltpak 500g',
                'code'          => 'SKU-SAL-001',
                'unit'          => 'packet',
                'description'   => 'Iodized table salt 500g',
                'unit_cost'     => 25.00,
                'unit_price'    => 40.00,
                'reorder_level' => 60,
            ],
            [
                'name'          => 'Ariel Powder 1kg',
                'code'          => 'SKU-DET-001',
                'unit'          => 'box',
                'description'   => 'Ariel washing powder 1kg',
                'unit_cost'     => 290.00,
                'unit_price'    => 350.00,
                'reorder_level' => 20,
            ],
            [
                'name'          => 'Blue Band 500g',
                'code'          => 'SKU-MAR-001',
                'unit'          => 'tub',
                'description'   => 'Blue Band margarine spread 500g',
                'unit_cost'     => 130.00,
                'unit_price'    => 170.00,
                'reorder_level' => 25,
            ],
            [
                'name'          => 'Royco Mchuzi Mix 200g',
                'code'          => 'SKU-ROY-001',
                'unit'          => 'packet',
                'description'   => 'Royco seasoning 200g packet',
                'unit_cost'     => 45.00,
                'unit_price'    => 65.00,
                'reorder_level' => 50,
            ],
            [
                'name'          => 'Coke 500ml',
                'code'          => 'SKU-BEV-001',
                'unit'          => 'bottle',
                'description'   => 'Coca-Cola 500ml PET bottle',
                'unit_cost'     => 50.00,
                'unit_price'    => 70.00,
                'reorder_level' => 100,
            ],
            [
                'name'          => 'Dettol 250ml',
                'code'          => 'SKU-DET-002',
                'unit'          => 'bottle',
                'description'   => 'Dettol antiseptic liquid 250ml',
                'unit_cost'     => 180.00,
                'unit_price'    => 230.00,
                'reorder_level' => 15,
            ],
            [
                'name'          => 'Indomie Noodles 70g',
                'code'          => 'SKU-NOO-001',
                'unit'          => 'packet',
                'description'   => 'Indomie instant noodles 70g',
                'unit_cost'     => 25.00,
                'unit_price'    => 40.00,
                'reorder_level' => 200,
            ],
        ];

        foreach ($skus as $sku) {
            Sku::create(array_merge($sku, ['is_active' => true]));
        }

        $this->command->info('✅ 10 SKUs seeded.');
    }
}
