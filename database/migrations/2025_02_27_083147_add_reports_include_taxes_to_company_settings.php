<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->boolean('reports_include_taxes')->default(true)->after('categories_has_store'); // Ajusta la posición según la estructura
        });
    }

    public function down()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn('reports_include_taxes');
        });
    }
};

