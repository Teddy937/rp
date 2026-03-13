<?php
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'code'          => $this->code,
            'unit'          => $this->unit,
            'description'   => $this->description,
            'unit_cost'     => $this->unit_cost,
            'unit_price'    => $this->unit_price,
            'reorder_level' => $this->reorder_level,
            'is_active'     => $this->is_active,
            'metadata'      => $this->metadata,
            'created_at'    => $this->created_at?->toDateTimeString(),
        ];
    }
}
