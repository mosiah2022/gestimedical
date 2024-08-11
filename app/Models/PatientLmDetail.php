<?php

namespace App\Models;

use App\Exports\ValueExport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;

class PatientLmDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        "product_id",
        "order_id",
        "patient_id",
        "prescription",
        "price_detail"
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function order() {
        return $this->belongsTo(PatientLm::class, 'order_id', 'id');
    }

    public function patient() {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }
}
