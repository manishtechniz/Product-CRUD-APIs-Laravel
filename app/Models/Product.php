<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    /**
     * Summary of timestamps
     */
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'discount',
        'status',
    ];

    /**
     * Product images relation
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}