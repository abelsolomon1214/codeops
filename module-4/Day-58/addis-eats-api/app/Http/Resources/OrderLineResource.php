<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'dish_id'           => $this->dish_id,
            'dish_name'         => $this->whenLoaded('dish', fn () => $this->dish->name),
            'qty'               => $this->qty,
            'unit_price_santim' => $this->unit_price_santim,
            'unit_price_etb'    => number_format($this->unit_price_santim / 100, 2),
            'line_total_santim' => $this->line_total_santim,
            'line_total_etb'    => number_format($this->line_total_santim / 100, 2),
        ];
    }
}
