<?php
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockLedgerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'store_id'     => $this->store_id,
            'sku_id'       => $this->sku_id,
            'quantity'     => (float) $this->quantity,
            'is_low_stock' => $this->sku
                ? $this->quantity <= $this->sku->reorder_level
                : false,
            'sku'          => new SkuResource($this->whenLoaded('sku')),
            'store'        => new StoreResource($this->whenLoaded('store')),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}
