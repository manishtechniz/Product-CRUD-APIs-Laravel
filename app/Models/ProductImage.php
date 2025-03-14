<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
