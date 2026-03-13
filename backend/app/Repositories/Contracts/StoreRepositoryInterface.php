<?php
namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface StoreRepositoryInterface
{
    public function allForBranch(int $branchId): Collection;
    public function paginateFiltered(array $filters, int $perPage): LengthAwarePaginator;
}
