<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cfes', function (Blueprint $table) {
            $table->unsignedBigInteger('main_cfe_id')->nullable()->after('qrUrl');
            $table->foreign('main_cfe_id')->references('id')->on('cfes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cfes', function (Blueprint $table) {
            $table->dropForeign(['main_cfe_id']);
            $table->dropColumn('main_cfe_id');
        });
    }
};
