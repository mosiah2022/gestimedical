<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientLm extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'company_id'       => $this->company_id,
            'patient_id'       => $this->patient_id,
            'date_ini'         => $this->date_ini,
            'authorized_by'    => $this->authorized_by,
            'observation'      => $this->observation,
            'phone_id'         => $this->phone_id,
            'address_id'       => $this->address_id,
            'diagnostic_id'    => $this->diagnostic_id,
            'lm_code'          => $this->lm_code,
            'doctor_name'      => $this->doctor_name,
            'orders'           => PatientLmDetail::collection($this->orders),
            'status'           => $this->status,
            'discount_percent' => $this->discount_percent
            //'patient'    => new Patient($this->patient)
        ];
    }
}
