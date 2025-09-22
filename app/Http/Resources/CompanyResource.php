<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Company */
class CompanyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            // agrega campos que quieras exponer:
            // 'nit' => $this->nit,
            // 'email' => $this->email,
            // 'status' => $this->status,
        ];
    }
}
