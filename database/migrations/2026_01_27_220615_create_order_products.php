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
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();

            $table->decimal('price', 10, 4)->default(0);
            $table->decimal('packaging_material_price', 10, 4)->default(0);
            $table->decimal('production_price', 10, 4)->default(0);
            $table->decimal('packaging_price', 10, 4)->default(0);
            $table->decimal('transportation_price', 10, 4)->default(0);
            $table->decimal('multi_delivery_price', 10, 4)->default(0);
            $table->decimal('sell_percent', 5, 2)->default(0);

            $table->timestamps();

            $table->unique(['order_id', 'product_id']); // один продукт = одна строка в заказе
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_products');
    }
};
