<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class Patient extends JsonResource
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
            'id'            => $this->id,
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'personal_id'   => $this->personal_id,
            'age'           => $this->age,
            'city_id'       => $this->city_id,
            'type_document' => $this->type_document,
            'sex'           => $this->sex,
            'birthday' => $this->birthday ? Carbon::parse($this->birthday)->format('Y-m-d') : null,
            'born_city_id'  => $this->born_city_id,
            'address'       => $this->address,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'type_user'     => $this->type_user,
            'disability'    => $this->disability,
            'full_name'     => $this->first_name . ' ' . $this->last_name,
            'created_at'    => $this->created_at->format('d/m/Y'),
        ];
    }
}
