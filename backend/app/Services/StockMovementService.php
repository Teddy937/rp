<?php
namespace App\Services;

use App\Models\Stock_movement;
use App\Models\User;
use App\Repositories\StockMovementRepository;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function __construct(
        private readonly StockMovementRepository $movementRepo
    ) {}

    /**
     * Record a sale (immediate completion, deducts stock instantly).
     */
    public function recordSale(array $data, User $actor): Stock_movement
    {
        return DB::transaction(function () use ($data, $actor) {
            return Stock_movement::create([
                'reference_no'   => generate_reference('SLE'),
                'type'           => Stock_movement::TYPE_SALE,
                'status'         => Stock_movement::STATUS_COMPLETED, // sales are immediate
                'from_store_id'  => $data['store_id'],
                'from_branch_id' => $data['branch_id'],
                'sku_id'         => $data['sku_id'],
                'quantity'       => $data['quantity'],
                'unit_cost'      => $data['unit_cost'] ?? 0,
                'total_cost'     => ($data['unit_cost'] ?? 0) * $data['quantity'],
                'notes'          => $data['notes'] ?? null,
                'created_by'     => $actor->id,
                'completed_at'   => now(),
            ]);
            // Observer fires → ledger updated
        });
    }

    /**
     * Initiate a transfer (inter-store or inter-branch).
     * Creates a PENDING transfer_out movement awaiting approval.
     */
    public function initiateTransfer(array $data, User $actor): Stock_movement
    {
        return DB::transaction(function () use ($data, $actor) {
            return Stock_movement::create([
                'reference_no'   => generate_reference('TRF'),
                'type'           => Stock_movement::TYPE_TRANSFER_OUT,
                'status'         => Stock_movement::STATUS_PENDING,
                'from_store_id'  => $data['from_store_id'],
                'from_branch_id' => $data['from_branch_id'],
                'to_store_id'    => $data['to_store_id'],
                'to_branch_id'   => $data['to_branch_id'],
                'sku_id'         => $data['sku_id'],
                'quantity'       => $data['quantity'],
                'unit_cost'      => $data['unit_cost'] ?? 0,
                'total_cost'     => ($data['unit_cost'] ?? 0) * $data['quantity'],
                'notes'          => $data['notes'] ?? null,
                'created_by'     => $actor->id,
            ]);
        });
    }

    /**
     * Approve a transfer: marks transfer_out as approved.
     * When completed, observer fires and ledger is updated for both stores.
     */
    public function approveTransfer(Stock_movement $movement, User $actor): Stock_movement
    {
        if (! $movement->isPending()) {
            throw new \DomainException("Only pending transfers can be approved.");
        }

        return DB::transaction(function () use ($movement, $actor) {
            $movement->update([
                'status'       => Stock_movement::STATUS_COMPLETED,
                'approved_by'  => $actor->id,
                'approved_at'  => now(),
                'completed_at' => now(),
            ]);

            // Create the matching transfer_in record for the destination
            Stock_movement::create([
                'reference_no'   => generate_reference('TRF'),
                'type'           => Stock_movement::TYPE_TRANSFER_IN,
                'status'         => Stock_movement::STATUS_COMPLETED,
                'from_store_id'  => $movement->from_store_id,
                'from_branch_id' => $movement->from_branch_id,
                'to_store_id'    => $movement->to_store_id,
                'to_branch_id'   => $movement->to_branch_id,
                'sku_id'         => $movement->sku_id,
                'quantity'       => $movement->quantity,
                'unit_cost'      => $movement->unit_cost,
                'total_cost'     => $movement->total_cost,
                'notes'          => "Transfer approved — ref: {$movement->reference_no}",
                'created_by'   => $actor->id,
                'approved_by'  => $actor->id,
                'approved_at'  => now(),
                'completed_at' => now(),
            ]);
            // Observer fires on both → ledger updated for source & destination

            return $movement->fresh();
        });
    }

    /**
     * Reject a transfer.
     */
    public function rejectTransfer(Stock_movement $movement, User $actor, string $reason): Stock_movement
    {
        if (! $movement->isPending()) {
            throw new \DomainException("Only pending transfers can be rejected.");
        }

        $movement->update([
            'status'           => Stock_movement::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_by'      => $actor->id,
            'approved_at'      => now(),
        ]);

        return $movement->fresh();
    }

    /**
     * Record a stock adjustment (in or out).
     */
    public function recordAdjustment(array $data, User $actor): Stock_movement
    {
        return DB::transaction(function () use ($data, $actor) {
            $type = $data['direction'] === 'in'
                ? Stock_movement::TYPE_ADJUSTMENT_IN
                : Stock_movement::TYPE_ADJUSTMENT_OUT;

            $storeKey  = $data['direction'] === 'in' ? 'to_store_id' : 'from_store_id';
            $branchKey = $data['direction'] === 'in' ? 'to_branch_id' : 'from_branch_id';

            return Stock_movement::create([
                'reference_no' => generate_reference('ADJ'),
                'type'         => $type,
                'status'       => Stock_movement::STATUS_COMPLETED,
                $storeKey      => $data['store_id'],
                $branchKey     => $data['branch_id'],
                'sku_id'       => $data['sku_id'],
                'quantity'     => $data['quantity'],
                'unit_cost'    => $data['unit_cost'] ?? 0,
                'total_cost'   => ($data['unit_cost'] ?? 0) * $data['quantity'],
                'notes'        => $data['notes'] ?? null,
                'created_by'   => $actor->id,
                'completed_at' => now(),
            ]);
        });
    }

    /**
     * Record a procurement (stock coming into a store from central purchasing).
     */
    public function recordProcurement(array $data, User $actor): Stock_movement
    {
        return DB::transaction(function () use ($data, $actor) {
            return Stock_movement::create([
                'reference_no' => generate_reference('PRO'),
                'type'         => Stock_movement::TYPE_PROCUREMENT,
                'status'       => Stock_movement::STATUS_COMPLETED,
                'to_store_id'  => $data['store_id'],
                'to_branch_id' => $data['branch_id'],
                'sku_id'       => $data['sku_id'],
                'quantity'     => $data['quantity'],
                'unit_cost'    => $data['unit_cost'] ?? 0,
                'total_cost'   => ($data['unit_cost'] ?? 0) * $data['quantity'],
                'notes'        => $data['notes'] ?? null,
                'created_by'   => $actor->id,
                'completed_at' => now(),
            ]);
        });
    }
}
