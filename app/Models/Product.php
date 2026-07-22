<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'part_number',
        'category_id',
        'supplier_id',
        'unit',
        'cost_price',
        'selling_price',
        'quantity',
        'reorder_level',
        'image',
        'is_active',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Check if this product is at or below its reorder level.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }
}
