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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 30)->unique(); // e.g. MOV-20260311-00001
            $table->enum('type', [
                'sale',
                'transfer_out',
                'transfer_in',
                'adjustment_in',
                'adjustment_out',
                'procurement',
            ]);
            $table->enum('status', [
                'pending',
                'approved',
                'completed',
                'cancelled',
                'rejected',
            ])->default('pending');

            $table->enum('level', ['first_approval', 'final_approval'])->default('first_approval');

            // Source (nullable for procurement/adjustment_in)
            $table->foreignId('from_store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Destination (nullable for sales/adjustment_out)
            $table->foreignId('to_store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Product
            $table->foreignId('sku_id')->constrained('skus');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);

            // Audit fields
            $table->text('notes')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Optimized indexes for reporting & traceability
            $table->index(['type', 'status', 'created_at']);
            $table->index(['from_store_id', 'sku_id', 'created_at']);
            $table->index(['to_store_id', 'sku_id', 'created_at']);
            $table->index(['sku_id', 'created_at']);
            $table->index('reference_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
