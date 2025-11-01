<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status'  => $this->status,
            'amount_decimal' => $this->amount_decimal,
            'placed_at' => $this->placed_at,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
