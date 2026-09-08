<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('price', 12, 4)->change();
            $table->decimal('size', 12, 4)->change();
            $table->decimal('kg_price', 12, 4)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change();
            $table->decimal('size', 8, 2)->change();
            $table->decimal('kg_price', 8, 2)->change();
        });
    }
};
