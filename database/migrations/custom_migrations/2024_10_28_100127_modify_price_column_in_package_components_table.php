<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_components', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        Schema::table('package_components', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable()->after('cost');
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
            $table->dropColumn('price');
        });

        Schema::table('package_components', function (Blueprint $table) {
            $table->decimal('price', 8, 2);
        });
    }
};
