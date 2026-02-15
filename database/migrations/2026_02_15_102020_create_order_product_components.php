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
        Schema::create('order_product_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_product_id')->constrained('order_products')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();

            $table->decimal('grams', 10, 2); // сколько грамм этого компонента в этом заказе
            $table->decimal('price_per_kg', 10, 4); // фиксируем цену на момент заказа

            $table->timestamps();

            $table->unique(['order_product_id', 'component_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_product_components');
    }
};
