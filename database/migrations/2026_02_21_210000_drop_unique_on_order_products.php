<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            // MySQL uses the composite unique index to enforce the order_id FK,
            // so we add a standalone index on order_id first.
            $table->index('order_id', 'order_products_order_id_index');
            $table->dropUnique('order_products_order_id_product_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->unique(['order_id', 'product_id']);
            $table->dropIndex('order_products_order_id_index');
        });
    }
};
