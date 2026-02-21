<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add cost columns to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('packaging_material', 10, 4)->default(0.45)->after('commission_pct');
            $table->decimal('production', 10, 4)->default(0.12)->after('packaging_material');
            $table->decimal('packaging', 10, 4)->default(0.08)->after('production');
            $table->decimal('transportation', 10, 4)->default(0.45)->after('packaging');
            $table->decimal('multi_delivery', 10, 4)->default(0.12)->after('transportation');
            $table->decimal('sell_percent', 5, 2)->default(30)->after('multi_delivery');
        });

        // Migrate existing data only if old cost columns still exist
        if (Schema::hasColumn('order_products', 'packaging_material_price')) {
            DB::statement("
                UPDATE orders
                SET
                    packaging_material = op.packaging_material_price,
                    production         = op.production_price,
                    packaging          = op.packaging_price,
                    transportation     = op.transportation_price,
                    multi_delivery     = op.multi_delivery_price,
                    sell_percent       = op.sell_percent
                FROM order_products op
                WHERE orders.id = op.order_id
                  AND op.id = (
                      SELECT MIN(id) FROM order_products WHERE order_id = orders.id
                  )
            ");

            Schema::table('order_products', function (Blueprint $table) {
                $table->dropColumn([
                    'packaging_material_price',
                    'production_price',
                    'packaging_price',
                    'transportation_price',
                    'multi_delivery_price',
                    'sell_percent',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->decimal('packaging_material_price', 10, 4)->default(0);
            $table->decimal('production_price', 10, 4)->default(0);
            $table->decimal('packaging_price', 10, 4)->default(0);
            $table->decimal('transportation_price', 10, 4)->default(0);
            $table->decimal('multi_delivery_price', 10, 4)->default(0);
            $table->decimal('sell_percent', 5, 2)->default(0);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'packaging_material',
                'production',
                'packaging',
                'transportation',
                'multi_delivery',
                'sell_percent',
            ]);
        });
    }
};
