<?php

namespace App\Http\Resources;

use App\Models\CompanyProductMetadata;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Session;

class Product extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $metadata = $this->metadata->first();
        $companyId = intval(Session::get('company'));

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'price'           => $this->price,
            'presentation_id' => $this->presentation_id,
            'full_name'       => $this->name,

            // Devuelve el objeto completo como recurso
            'product_metadata' => $metadata
                ? new ProductMetadataResource($metadata)
                : null,
            'exists_in_metadata' => $this->existsInCompanyMetadata($companyId),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
