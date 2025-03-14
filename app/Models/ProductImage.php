<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;
    
    /**
     * Summary of timestamps
     */
    public $timestamps = true;

    protected $fillable = [
        'path',
    ];

    /**
     * Expose attributes to json response
     */
    protected $appends = [
        'image_url',
    ];
    
    /**
     * Get the image url attribute
     */
    public function getImageUrlAttribute()
    {
        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->path;
        }

        return url(Storage::url($this->path));
    }

    /**
     * Get the product that the image belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
