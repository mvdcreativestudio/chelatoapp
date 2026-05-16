<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('price_lists')) {
            Schema::create('price_lists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('currency')->default('Peso');
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            });
            return;
        }

        // Tabla preexistente: agregar columnas faltantes sin perder datos.
        if (!Schema::hasColumn('price_lists', 'currency')) {
            Schema::table('price_lists', function (Blueprint $table) {
                $table->string('currency')->default('Peso')->after('description');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_lists');
    }
};
