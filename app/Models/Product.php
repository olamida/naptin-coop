<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'unit_price',
        'stock_quantity',
        'enabled',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'stock_quantity' => 'integer',
        'enabled' => 'boolean',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return '';
    }
}
