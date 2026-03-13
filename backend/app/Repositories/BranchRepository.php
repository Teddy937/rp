<?php
namespace App\Repositories;

use App\Models\Branch;
use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{
    public function __construct(Branch $model)
    {
        parent::__construct($model);
    }

    public function allActive(): Collection
    {
        return $this->query()->active()->with('stores')->get();
    }

    public function paginateWithStores(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['stores' => fn($q) => $q->active()])
            ->withCount('stores')
            ->latest()
            ->paginate($perPage);
    }
}
