<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $sale_number
 * @property int|null $customer_id
 * @property int $user_id
 * @property string $payment_method
 * @property string|null $momo_reference
 * @property float $subtotal
 * @property float $discount
 * @property float $total
 * @property float|null $amount_tendered
 * @property float|null $change_given
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'customer_id',
        'user_id',
        'payment_method',
        'momo_reference',
        'subtotal',
        'discount',
        'total',
        'amount_tendered',
        'change_given',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_tendered' => 'decimal:2',
        'change_given' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replacements()
    {
        return $this->hasMany(SaleReplacement::class, 'original_sale_id');
    }

    public function replacementSource()
    {
        return $this->hasMany(SaleReplacement::class, 'replacement_sale_id');
    }

    /**
     * Check if this sale can be returned/exchanged
     * Business rule: must be within 30 days of purchase
     */
    public function isReturnable(): bool
    {
        if ($this->status !== 'completed') {
            return false;
        }

        return $this->created_at->diffInDays(now()) <= 30;
    }

    /**
     * Check if this specific sale item can be returned
     */
    public function hasReplacements(): bool
    {
        return $this->replacements()->exists();
    }

    /**
     * Process a simple full refund (reverse the sale)
     */
    public function refund(): void
    {
        $user = auth()->user();
        \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
            // Restore stock
            foreach ($this->items as $item) {
                $item->product->increment('quantity', $item->quantity);
            }

            // Mark as refunded
            $this->update(['status' => 'refunded']);

            // Log activity
            activity()
                ->causedBy($user)
                ->performedOn($this)
                ->withProperties([
                    'amount' => $this->total,
                    'payment_method' => $this->payment_method,
                ])
                ->log('sale_refunded');
        });
    }
}
