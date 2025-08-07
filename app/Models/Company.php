<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
    
    /**
     * Relación directa con la tabla pivote company_product_metadata
     */
    public function companyProductMetadata(): HasMany
    {
        return $this->hasMany(CompanyProductMetadata::class);
    }

    /**
     * Relación muchos-a-muchos con ProductMetadata a través de la tabla pivote
     */
    public function productMetadata(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductMetadata::class,
            'company_product_metadata',
            'company_id',
            'product_metadata_id'
        )->withPivot('product_id');
    }
}
