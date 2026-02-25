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
            $table->enum('price_type', ['rental_flat', 'rental_tiered', 'sell_once'])
                  ->default('rental_flat')
                  ->after('description');
            
            $table->decimal('promo_price', 15, 2)->nullable()->after('price_per_day');
            $table->decimal('tier_price', 15, 2)->nullable()->after('promo_price');
            $table->decimal('tier_promo_price', 15, 2)->nullable()->after('tier_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'price_type',
                'promo_price',
                'tier_price',
                'tier_promo_price'
            ]);
        });
    }
};
