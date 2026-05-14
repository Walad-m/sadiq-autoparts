<?php

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SufficientStock implements ValidationRule
{
    /**
     * Validate that all items in the cart have sufficient stock.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            return;
        }

        foreach ($value as $index => $item) {
            if (!isset($item['product_id'], $item['quantity'])) {
                continue;
            }

            $product = Product::find($item['product_id']);

            if (!$product) {
                $fail("items.{$index}.product_id: Product not found.");
                return;
            }

            if ($product->quantity < $item['quantity']) {
                $fail("Insufficient stock for {$product->name}. Available: {$product->quantity}, Requested: {$item['quantity']}");
                return;
            }
        }
    }
}
