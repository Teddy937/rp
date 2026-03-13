<?php
namespace App\Repositories\Contracts;

interface StockLedgerRepositoryInterface
{
    public function getStoreStock(int $storeId, array $filters = []);
    public function getStockForStoreSku(int $storeId, int $skuId);
    public function getLowStockItems(int $storeId);
}
