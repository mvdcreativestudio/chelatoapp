<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCostAndPriceToPackageComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_components', function (Blueprint $table) {
            $table->decimal('cost', 8, 2)->after('stock'); // Agrega la columna cost después de stock
            $table->decimal('price', 8, 2)->after('cost'); // Agrega la columna price después de cost
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_components', function (Blueprint $table) {
            $table->dropColumn('cost');
            $table->dropColumn('price');
        });
    }
}
