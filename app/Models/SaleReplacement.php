<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_sale_id',
        'replacement_sale_id',
        'returned_items',
        'new_items',
        'refund_amount',
        'additional_charge',
        'reason',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'returned_items' => 'array',
        'new_items' => 'array',
        'refund_amount' => 'decimal:2',
        'additional_charge' => 'decimal:2',
    ];

    public function originalSale()
    {
        return $this->belongsTo(Sale::class, 'original_sale_id');
    }

    public function replacementSale()
    {
        return $this->belongsTo(Sale::class, 'replacement_sale_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the net amount: positive = refund to customer, negative = customer pays
     */
    public function netAmount(): float
    {
        return (float)($this->refund_amount - $this->additional_charge);
    }

    public function isRefund(): bool
    {
        return $this->refund_amount > 0 && $this->additional_charge == 0;
    }

    public function isUpgrade(): bool
    {
        return $this->additional_charge > 0 && $this->refund_amount == 0;
    }

    public function isEvenExchange(): bool
    {
        return $this->refund_amount == 0 && $this->additional_charge == 0;
    }
}
