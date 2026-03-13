<?php
namespace App\Repositories;

use App\Models\Stock_movement;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StockMovementRepository extends BaseRepository implements StockMovementRepositoryInterface
{
    public function __construct(Stock_movement $model)
    {
        parent::__construct($model);
    }

    public function paginateFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()->with([
            'sku',
            'fromStore.branch',
            'toStore.branch',
            'createdBy',
            'approvedBy',
        ]);

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['store_id'])) {
            $query->forStore($filters['store_id']);
        }

        if (! empty($filters['sku_id'])) {
            $query->where('sku_id', $filters['sku_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['reference_no'])) {
            $query->where('reference_no', 'like', "%{$filters['reference_no']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function findByReference(string $reference): ?Stock_movement
    {
        return $this->query()
            ->with(['sku', 'fromStore', 'toStore', 'createdBy'])
            ->where('reference_no', $reference)
            ->first();
    }

    public function getPendingTransfers(array $filters = []): Collection
    {
        $query = $this->query()
            ->with(['sku', 'fromStore.branch', 'toStore.branch', 'createdBy'])
            ->where('status', Stock_movement::STATUS_PENDING)
            ->whereIn('type', [Stock_movement::TYPE_TRANSFER_OUT, Stock_movement::TYPE_TRANSFER_IN]);

        if (! empty($filters['branch_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('from_branch_id', $filters['branch_id'])
                    ->orWhere('to_branch_id', $filters['branch_id']);
            });
        }

        return $query->latest()->get();
    }
}
