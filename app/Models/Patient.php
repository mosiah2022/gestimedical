<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'personal_id',
        'age',
        'city_id'
    ];

    //Relationship
    public function city() {
        return $this->belongsTo(City::class);
    }

    public function patient_lms() {
       return $this->hasMany(PatientLm::class, 'patient_id', 'id');
    }

    public function patient_adress() {
        return $this->hasMany(PatientAdress::class)->where('category','address');
    }

    public function patient_phone() {
        return $this->hasMany(PatientAdress::class)->where('category','phone');
    }

    //Query Scope
    public function scopePatientByDate($query, $dateIni, $dateEnd) {
        if($dateIni !== 'null')
            return $query->whereBetween('created_at', [$dateIni,$dateEnd]);
    }
}
