<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientLm extends Model
{
    use HasFactory;

    protected $fillable=[
        'company_id',
        'patient_id',
        'date_ini',
        'date_end',
        'diagnostic_id',
        'lm_code',
        'doctor_name',
        'authorized_by',
        'observation',
        'phone_id',
        'address_id',
        'discount_percent',
        'invoice_number'
    ];

    public function patient(){
        return $this->belongsTo(Patient::class);
    }

    public function orders(){
        return $this->hasMany(PatientLmDetail::class,  'order_id', 'id');
    }
}
