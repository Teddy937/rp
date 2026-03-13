<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Sku;
use App\Models\Stock_ledger;
use App\Models\Stock_movement;
use App\Models\Store;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        try {
            // ── Summary Cards ────────────────────────────────────────────
            $totalSalesToday = Stock_movement::where('type', Stock_movement::TYPE_SALE)
                ->where('status', Stock_movement::STATUS_COMPLETED)
                ->whereDate('created_at', today())
                ->count();

            $totalSalesMonth = Stock_movement::where('type', Stock_movement::TYPE_SALE)
                ->where('status', Stock_movement::STATUS_COMPLETED)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $pendingTransfers = Stock_movement::whereIn('type', [
                Stock_movement::TYPE_TRANSFER_OUT,
                Stock_movement::TYPE_TRANSFER_IN,
            ])
                ->where('status', Stock_movement::STATUS_PENDING)
                ->count();

            $procurementMonth = Stock_movement::where('type', Stock_movement::TYPE_PROCUREMENT)
                ->where('status', Stock_movement::STATUS_COMPLETED)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $totalSkus     = Sku::where('is_active', true)->count();
            $lowStockCount = Stock_ledger::lowStock()->count();
            $outOfStock    = Stock_ledger::where('quantity', '<=', 0)->count();
            $totalUsers    = User::where('account_status', 'active')->count();
            $totalBranches = Branch::where('is_active', true)->count();
            $totalStores   = Store::where('is_active', true)->count();

            // ── Movements Over Time — last 30 days ───────────────────────
            $movementsOverTime = Stock_movement::where('status', Stock_movement::STATUS_COMPLETED)
                ->where('created_at', '>=', now()->subDays(29)->startOfDay())
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw("SUM(CASE WHEN type = 'sale'        THEN 1 ELSE 0 END) as sales"),
                    DB::raw("SUM(CASE WHEN type = 'procurement' THEN 1 ELSE 0 END) as procurement"),
                    DB::raw("SUM(CASE WHEN type IN ('transfer_in','transfer_out') THEN 1 ELSE 0 END) as transfers"),
                    DB::raw("SUM(CASE WHEN type IN ('adjustment_in','adjustment_out') THEN 1 ELSE 0 END) as adjustments")
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // ── Movement Breakdown by Type — this month ──────────────────
            $movementBreakdown = Stock_movement::where('status', Stock_movement::STATUS_COMPLETED)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->select('type', DB::raw('COUNT(*) as total'))
                ->groupBy('type')
                ->get()
                ->mapWithKeys(fn($row) => [$row->type => (int) $row->total]);

            // ── Daily Sales Quantity — last 14 days ──────────────────────
            $dailySalesQty = Stock_movement::where('type', Stock_movement::TYPE_SALE)
                ->where('status', Stock_movement::STATUS_COMPLETED)
                ->where('created_at', '>=', now()->subDays(13)->startOfDay())
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(quantity) as total_qty')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // ── Stock Levels per Store ────────────────────────────────────
            $stockPerStore = Stock_ledger::with('store:id,name')
                ->select('store_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('COUNT(*) as sku_count'))
                ->groupBy('store_id')
                ->get()
                ->map(fn($row) => [
                    'store'     => $row->store?->name,
                    'total_qty' => (float) $row->total_qty,
                    'sku_count' => (int) $row->sku_count,
                ]);

            // ── Movements by Status ──────────────────────────────────────
            $movementsByStatus = Stock_movement::select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->get()
                ->mapWithKeys(fn($row) => [$row->status => (int) $row->total]);

            // ── Top 10 SKUs by Sales Quantity — this month ───────────────
            $topSkus = Stock_movement::where('type', Stock_movement::TYPE_SALE)
                ->where('status', Stock_movement::STATUS_COMPLETED)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->with('sku:id,name,code')
                ->select('sku_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('COUNT(*) as tx_count'))
                ->groupBy('sku_id')
                ->orderByDesc('total_qty')
                ->limit(10)
                ->get()
                ->map(fn($row) => [
                    'name'      => $row->sku?->name,
                    'code'      => $row->sku?->code,
                    'total_qty' => (float) $row->total_qty,
                    'tx_count'  => (int) $row->tx_count,
                ]);

            // ── Top 5 Low Stock SKUs ─────────────────────────────────────
            $lowStockItems = Stock_ledger::with(['sku:id,name,code,unit,reorder_level', 'store:id,name'])
                ->lowStock()
                ->orderByRaw('quantity ASC')
                ->limit(5)
                ->get()
                ->map(fn($item) => [
                    'sku_name'      => $item->sku?->name,
                    'sku_code'      => $item->sku?->code,
                    'unit'          => $item->sku?->unit,
                    'store_name'    => $item->store?->name,
                    'quantity'      => (float) $item->quantity,
                    'reorder_level' => (int) $item->sku?->reorder_level,
                    'shortage'      => max(0, $item->sku?->reorder_level - $item->quantity),
                ]);

            // ── Recent Movements — last 8 ────────────────────────────────
            $recentMovements = Stock_movement::with(['sku:id,name,code', 'createdBy:id,name'])
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn($m) => [
                    'id'           => $m->id,
                    'reference_no' => $m->reference_no,
                    'type'         => $m->type,
                    'status'       => $m->status,
                    'sku_name'     => $m->sku?->name,
                    'quantity'     => $m->quantity,
                    'created_by'   => $m->createdBy?->name,
                    'created_at'   => $m->created_at?->toDateTimeString(),
                ]);

            return $this->success([
                'summary'             => [
                    'sales_today'       => $totalSalesToday,
                    'sales_this_month'  => $totalSalesMonth,
                    'pending_transfers' => $pendingTransfers,
                    'procurement_month' => $procurementMonth,
                    'total_skus'        => $totalSkus,
                    'low_stock'         => $lowStockCount,
                    'out_of_stock'      => $outOfStock,
                    'total_users'       => $totalUsers,
                    'total_branches'    => $totalBranches,
                    'total_stores'      => $totalStores,
                ],
                'movements_over_time' => $movementsOverTime,
                'movement_breakdown'  => $movementBreakdown,
                'daily_sales_qty'     => $dailySalesQty,
                'stock_per_store'     => $stockPerStore,
                'movements_by_status' => $movementsByStatus,
                'top_skus'            => $topSkus,
                'low_stock_items'     => $lowStockItems,
                'recent_movements'    => $recentMovements,
            ]);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
