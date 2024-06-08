<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDiagnostic extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'patient_id',
        'company_id'
    ];

    public function patient() {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
