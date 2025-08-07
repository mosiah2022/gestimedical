<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProductMetadata extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_id',
        'product_metadata_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function metadata()
    {
        return $this->belongsTo(ProductMetadata::class, 'product_metadata_id');
    }
}
