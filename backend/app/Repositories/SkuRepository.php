<?php
namespace App\Repositories;

use App\Models\Sku;
use App\Repositories\Contracts\SkuRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SkuRepository extends BaseRepository implements SkuRepositoryInterface
{
    public function __construct(Sku $model)
    {
        parent::__construct($model);
    }

    public function paginateFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findByCode(string $code): ?Sku
    {
        return $this->query()->where('code', $code)->first();
    }
}
