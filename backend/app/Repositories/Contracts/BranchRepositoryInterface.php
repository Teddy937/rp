<?php
namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BranchRepositoryInterface
{
    public function allActive(): Collection;
    public function paginateWithStores(int $perPage): LengthAwarePaginator;
}

interface StoreRepositoryInterface
{
    public function allForBranch(int $branchId): Collection;
    public function paginateFiltered(array $filters, int $perPage): LengthAwarePaginator;
}

interface SkuRepositoryInterface
{
    public function paginateFiltered(array $filters, int $perPage): LengthAwarePaginator;
    public function findByCode(string $code);
}

interface StockLedgerRepositoryInterface
{
    public function getStoreStock(int $storeId, array $filters = []);
    public function getStockForStoreSku(int $storeId, int $skuId);
    public function getLowStockItems(int $storeId);
}

interface StockMovementRepositoryInterface
{
    public function paginateFiltered(array $filters, int $perPage): LengthAwarePaginator;
    public function findByReference(string $reference);
    public function getPendingTransfers(array $filters = []): Collection;
}
