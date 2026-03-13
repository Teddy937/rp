<?php
namespace App\Providers;

use App\Models\Sku;
use App\Models\Stock_movement;
use App\Observers\SkuObserver;
use App\Observers\StockMovementObserver;
use App\Repositories\BranchRepository;
use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\SkuRepositoryInterface;
use App\Repositories\Contracts\StockLedgerRepositoryInterface;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Repositories\Contracts\StoreRepositoryInterface;
use App\Repositories\SkuRepository;
use App\Repositories\StockLedgerRepository;
use App\Repositories\StockMovementRepository;
use App\Repositories\StoreRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ── Repository Bindings ──────────────────────────────────────────
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(StoreRepositoryInterface::class, StoreRepository::class);
        $this->app->bind(SkuRepositoryInterface::class, SkuRepository::class);
        $this->app->bind(StockLedgerRepositoryInterface::class, StockLedgerRepository::class);
        $this->app->bind(StockMovementRepositoryInterface::class, StockMovementRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Observers ────────────────────────────────────────────────────
        Stock_movement::observe(StockMovementObserver::class);
        Sku::observe(SkuObserver::class);

    }
}
