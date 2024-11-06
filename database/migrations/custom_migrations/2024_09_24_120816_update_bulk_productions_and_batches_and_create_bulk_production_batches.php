<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBulkProductionsAndBatchesAndCreateBulkProductionBatches extends Migration
{
    public function up()
    {
        // Modificar bulk_productions
        Schema::table('bulk_productions', function (Blueprint $table) {
            $table->decimal('quantity_produced', 8, 2)->change();
            $table->decimal('quantity_used', 8, 2)->change();
            $table->dropForeign(['batch_id']); // Eliminar la FK si existe
            $table->dropColumn('batch_id');    // Eliminar la columna
        });

        // Modificar batches
        Schema::table('batches', function (Blueprint $table) {
            $table->decimal('quantity', 8, 2)->change();
        });

        // Crear bulk_production_batches
        Schema::create('bulk_production_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bulk_productions_id');
            $table->unsignedBigInteger('batch_id');
            $table->decimal('quantity_used', 8, 2);
            $table->timestamps();

            // Claves foráneas
            $table->foreign('bulk_productions_id')->references('id')->on('bulk_productions')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Revertir los cambios
        Schema::table('bulk_productions', function (Blueprint $table) {
            $table->integer('quantity_produced')->change();
            $table->integer('quantity_used')->change();
            $table->unsignedBigInteger('batch_id');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::dropIfExists('bulk_production_batches');
    }
}