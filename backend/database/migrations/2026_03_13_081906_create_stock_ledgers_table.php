<?php

use App\Models\Sku;
use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Store::class);
            $table->foreignIdFor(Sku::class);
            $table->decimal('quantity', 15, 4)->default(0); // supports fractional units
            $table->unique(['store_id', 'sku_id']);         // enforced: only one row per store-sku
            $table->index(['store_id', 'sku_id', 'quantity']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
