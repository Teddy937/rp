<?php

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
        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('unit', 30)->default('piece'); // piece, kg, litre, bag, etc.
            $table->text('description')->nullable();
            $table->decimal('unit_cost', 18, 2)->default(0.00);
            $table->decimal('unit_price', 18, 2)->default(0.00);
            $table->unsignedInteger('reorder_level')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // flexible attributes for future scale
            $table->timestamps();
            $table->softDeletes();
            $table->index('code');
            $table->index('is_active');
            $table->fullText(['name', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skus');
    }
};
