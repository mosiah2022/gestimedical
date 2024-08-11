<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientLmDetail extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'prescription' => $this->prescription,
            'price_detail' => $this->price_detail,
            'products'     => Product::make($this->product)
        ];
    }
}
