<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxRatesTable extends Migration
{
    public function up()
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 5, 2); // Valor porcentual: 10.00, 22.00, etc.
            $table->timestamps();
        });

        // Insertar datos iniciales
        DB::table('tax_rates')->insert([
            ['name' => 'IVA 0', 'rate' => 0.00],
            ['name' => 'IVA Mínimo', 'rate' => 10.00],
            ['name' => 'IVA Tasa Básica', 'rate' => 22.00],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('tax_rates');
    }
}
