<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNameColumnFromProductFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('product_features', function (Blueprint $table) {
            if (Schema::hasColumn('product_features', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('product_features', function (Blueprint $table) {
            if (!Schema::hasColumn('product_features', 'name')) {
                $table->string('name')->nullable();
            }
        });
    }
}
