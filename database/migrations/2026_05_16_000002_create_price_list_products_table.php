<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('price_list_products')) {
            Schema::create('price_list_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('price_list_id');
                $table->unsignedBigInteger('product_id');
                $table->decimal('price', 12, 2);
                $table->timestamps();

                $table->foreign('price_list_id')->references('id')->on('price_lists')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->unique(['price_list_id', 'product_id']);
            });
            return;
        }

        // Tabla preexistente: agregar unique compuesto si no existe.
        $indexName = 'price_list_products_price_list_id_product_id_unique';
        $exists = collect(DB::select("SHOW INDEX FROM `price_list_products` WHERE Key_name = ?", [$indexName]))->isNotEmpty();
        if (!$exists) {
            // Limpiar duplicados (price_list_id, product_id) antes de crear la unique.
            DB::statement("
                DELETE p1 FROM price_list_products p1
                INNER JOIN price_list_products p2
                  ON p1.price_list_id = p2.price_list_id
                 AND p1.product_id = p2.product_id
                 AND p1.id > p2.id
            ");
            Schema::table('price_list_products', function (Blueprint $table) {
                $table->unique(['price_list_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_products');
    }
};
