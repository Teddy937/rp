<?php
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'reference_no'     => $this->reference_no,
            'type'             => $this->type,
            'status'           => $this->status,
            'quantity'         => (float) $this->quantity,
            'unit_cost'        => (float) $this->unit_cost,
            'total_cost'       => (float) $this->total_cost,
            'notes'            => $this->notes,
            'rejection_reason' => $this->rejection_reason,
            'sku'              => new SkuResource($this->whenLoaded('sku')),
            'from_store'       => new StoreResource($this->whenLoaded('fromStore')),
            'to_store'         => new StoreResource($this->whenLoaded('toStore')),
            'created_by'       => new UserResource($this->whenLoaded('createdBy')),
            'approved_by'      => new UserResource($this->whenLoaded('approvedBy')),
            'approved_at'      => $this->approved_at?->toDateTimeString(),
            'completed_at'     => $this->completed_at?->toDateTimeString(),
            'created_at'       => $this->created_at?->toDateTimeString(),
        ];
    }
}
