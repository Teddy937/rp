<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\StockLedgerResource;
use App\Repositories\StockLedgerRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockLedgerController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly StockLedgerRepository $ledgerRepo
    ) {}

    /**
     * Get stock for a specific store.
     */
    public function storeStock(Request $request, int $storeId): JsonResponse
    {
        try {
            $user    = Auth::user();
            $filters = $request->only(['sku_id', 'search']);
            $stock   = $this->ledgerRepo->getStoreStock($storeId, $filters);

            return $this->created(StockLedgerResource::collection($stock));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Get current stock for a single SKU in a store.
     */
    public function skuStock(int $storeId, int $skuId): JsonResponse
    {
        try {
            $user   = Auth::user();
            $ledger = $this->ledgerRepo->getStockForStoreSku($storeId, $skuId);
            if (! $ledger) {
                return $this->success(['quantity' => 0, 'store_id' => $storeId, 'sku_id' => $skuId]);
            }

            return $this->created(new StockLedgerResource($ledger->load(['sku', 'store'])));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Get low stock alerts for a store.
     */
    public function lowStock(int $storeId): JsonResponse
    {
        try {
            $user = Auth::user();

            $items = $this->ledgerRepo->getLowStockItems($storeId);

            return $this->created(StockLedgerResource::collection($items));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
