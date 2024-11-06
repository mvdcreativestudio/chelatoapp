<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('formula_raw_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('raw_material_id')->nullable()->change();
            $table->decimal('quantity_required', 8, 2)->nullable()->change();
            $table->string('clarification', 255)->after('quantity_required');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('formula_raw_materials', function (Blueprint $table) {
            $table->dropForeign(['raw_material_id']);
            $table->unsignedBigInteger('raw_material_id')->nullable(false)->change();
            
            // Cambiar de nuevo quantity_required a int y hacerlo no nullable
            $table->integer('quantity_required')->nullable(false)->change();
            
            // Eliminar la columna clarification
            $table->dropColumn('clarification');
        });
    }

};
