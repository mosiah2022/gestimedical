<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'name',
        'presentation',
        'price',
        'presentation_id'
    ];

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relación muchos-a-muchos con ProductMetadata a través de company_product_metadata
     */
    public function metadata(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductMetadata::class,
            'company_product_metadata',
            'product_id',
            'product_metadata_id'
        )->withPivot('company_id');
    }

    public function companyProductMetadata()
    {
        return $this->hasMany(CompanyProductMetadata::class);
    }

    public function existsInCompanyMetadata(int $companyId): bool
    {
        return $this->companyProductMetadata()
            ->where('company_id', $companyId)
            ->exists();
    }

}
