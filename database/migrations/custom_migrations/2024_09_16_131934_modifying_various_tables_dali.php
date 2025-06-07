<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyingVariousTablesDali  extends Migration
{
    public function up()
    {
        // Cambios en la tabla 'purchase_entries'
        Schema::table('purchase_entries', function (Blueprint $table) {
            // Quitar la FK y luego eliminar la columna 'supplier_invoice_id'
            $table->dropForeign(['supplier_invoice_id']);
            $table->dropColumn('supplier_invoice_id');

            // Quitar la FK y luego eliminar la columna 'batch_id'
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');

            // Agregar la columna 'purchase_order_items_id' después de 'entry_date'
            $table->unsignedBigInteger('purchase_order_items_id')->after('entry_date')->nullable();

            // Crear la FK para 'purchase_order_items_id'
            $table->foreign('purchase_order_items_id')
                  ->references('id')
                  ->on('purchase_order_items')
                  ->onDelete('set null');
        });

        // Cambios en la tabla 'batches'
        Schema::table('batches', function (Blueprint $table) {
            

            // Agregar la columna 'purchase_entries_id' después de 'expiration_date'
            $table->unsignedBigInteger('purchase_entries_id')->after('expiration_date')->nullable();

            // Crear la FK para 'purchase_entries_id'
            $table->foreign('purchase_entries_id')
                  ->references('id')
                  ->on('purchase_entries')
                  ->onDelete('set null');


            // Quitar la FK y luego eliminar la columna 'purchase_order_items_id'
            $table->dropForeign(['purchase_order_items_id']);
            $table->dropColumn('purchase_order_items_id');

            // Hacer que la columna 'production_date' sea nullable
            $table->timestamp('production_date')->nullable()->change();
        });

        // Cambios en la tabla 'purchase_order_items'
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Agregar la columna 'product_id' después de 'raw_material_id'
            $table->unsignedBigInteger('product_id')->after('raw_material_id')->nullable();

            // Crear la FK para 'product_id'
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('set null');

            // Hacer que la columna 'raw_material_id' sea nullable
            $table->unsignedBigInteger('raw_material_id')->nullable()->change();
        });
    }

    public function down()
    {
        // Revertir los cambios en la tabla 'purchase_entries'
        Schema::table('purchase_entries', function (Blueprint $table) {
            // Revertir la FK y la columna 'purchase_order_items_id'
            $table->dropForeign(['purchase_order_items_id']);
            $table->dropColumn('purchase_order_items_id');

            // Revertir la FK y la columna 'batch_id'
            $table->unsignedBigInteger('batch_id')->after('entry_date');
            $table->foreign('batch_id')
                  ->references('id')
                  ->on('batches')
                  ->onDelete('set null');

            // Revertir la FK y la columna 'supplier_invoice_id'
            $table->unsignedBigInteger('supplier_invoice_id')->after('entry_date');
            $table->foreign('supplier_invoice_id')
                  ->references('id')
                  ->on('supplier_invoices')
                  ->onDelete('set null');
        });

        // Revertir los cambios en la tabla 'batches'
        Schema::table('batches', function (Blueprint $table) {
            // Revertir la FK y la columna 'purchase_entries_id'
            $table->dropForeign(['purchase_entries_id']);
            $table->dropColumn('purchase_entries_id');


            // Revertir la FK y la columna 'purchase_order_items_id'
            $table->unsignedBigInteger('purchase_order_items_id')->nullable();
            $table->foreign('purchase_order_items_id')
                  ->references('id')
                  ->on('purchase_order_items')
                  ->onDelete('set null');

            // Hacer que la columna 'production_date' no sea nullable
            $table->timestamp('production_date')->nullable(false)->change();
        });

        // Revertir los cambios en la tabla 'purchase_order_items'
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Revertir la FK y la columna 'product_id'
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');

            // Revertir la columna 'raw_material_id' a no nullable
            $table->unsignedBigInteger('raw_material_id')->nullable(false)->change();
        });
    }
}

