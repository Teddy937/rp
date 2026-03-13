<?php
namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface StockMovementRepositoryInterface
{
    public function paginateFiltered(array $filters, int $perPage): LengthAwarePaginator;
    public function findByReference(string $reference);
    public function getPendingTransfers(array $filters = []): Collection;
}
