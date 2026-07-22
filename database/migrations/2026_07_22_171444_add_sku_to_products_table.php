<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('id');
        });

        // Backfill existing products with a unique SKU
        $products = \Illuminate\Support\Facades\DB::table('products')->whereNull('sku')->get();
        foreach ($products as $product) {
            \Illuminate\Support\Facades\DB::table('products')
                ->where('id', $product->id)
                ->update(['sku' => 'PRD-' . strtoupper(\Illuminate\Support\Str::random(8))]);
        }

        // Make it non-nullable after backfill
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }
};
