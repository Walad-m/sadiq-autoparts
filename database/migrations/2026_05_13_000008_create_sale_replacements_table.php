<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('replacement_sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->json('returned_items'); // [{product_id, quantity, unit_price}, ...]
            $table->json('new_items'); // [{product_id, quantity, selling_price}, ...]
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->decimal('additional_charge', 10, 2)->default(0);
            $table->enum('reason', ['defective', 'wrong_size', 'wrong_item', 'customer_request', 'damaged']);
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_replacements');
    }
};
