<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'status'              => $this->status->value,
            'area'                => $this->area,
            'phone'               => $this->phone,
            'restaurant_id'       => $this->restaurant_id,
            'restaurant'          => $this->whenLoaded('restaurant', fn () => $this->restaurant->name),
            'subtotal_santim'     => $this->subtotal_santim,
            'subtotal_etb'        => number_format($this->subtotal_santim / 100, 2),
            'delivery_fee_santim' => $this->delivery_fee_santim,
            'delivery_fee_etb'    => number_format($this->delivery_fee_santim / 100, 2),
            'total_santim'        => $this->total_santim,
            'total_etb'           => number_format($this->total_santim / 100, 2),
            'notes'               => $this->notes,
            'lines'               => OrderLineResource::collection($this->whenLoaded('lines')),
            'created_at'          => $this->created_at?->toIso8601String(),
        ];
    }
}
