<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProductMetadataResource extends JsonResource
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
            'id'                   => $this->id,
            'company_id'           => $this->company_id,
            'product_id'           => $this->product_id,
            'product_metadata_id'  => $this->product_metadata_id,
            'metadata_code'        => $this->metadata->code ?? null,
        ];
    }
}
