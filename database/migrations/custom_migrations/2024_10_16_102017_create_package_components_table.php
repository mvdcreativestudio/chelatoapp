<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_components', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la tapita o etiqueta
            $table->string('description')->nullable(); // Descripción opcional
            $table->enum('type', ['cap', 'label']); // Tipo: cap (tapita) o label (etiqueta)
            $table->integer('stock'); // Cantidad en stock
            $table->timestamps(); // Timestamps para created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_components');
    }
}
