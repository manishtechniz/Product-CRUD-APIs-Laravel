<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /**
     * Summary of timestamps
     */
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock'
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}