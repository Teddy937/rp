<?php
namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface SkuRepositoryInterface
{
    public function paginateFiltered(array $filters, int $perPage): LengthAwarePaginator;
    public function findByCode(string $code);
}
