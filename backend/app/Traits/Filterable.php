<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Apply filters from request to a query.
     * Usage: $this->applyFilters($query, $request->only(['search', 'is_active']))
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            match ($key) {
                'search'    => $this->applySearch($query, $value),
                'is_active' => $query->where('is_active', filter_var($value, FILTER_VALIDATE_BOOLEAN)),
                'branch_id' => $query->where('branch_id', $value),
                'store_id'  => $query->where('store_id', $value),
                'sku_id'    => $query->where('sku_id', $value),
                'type'      => $query->where('type', $value),
                'status'    => $query->where('status', $value),
                'date_from' => $query->whereDate('created_at', '>=', $value),
                'date_to'   => $query->whereDate('created_at', '<=', $value),
                default     => null,
            };
        }

        return $query;
    }

    /**
     * Generic search across name and code fields.
     */
    private function applySearch(Builder $query, string $term): void
    {
        $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%");
        });
    }
}
