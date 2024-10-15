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
        Schema::table('company_settings', function (Blueprint $table) {
          $table->string('logo')->nullable()->change();
          $table->renameColumn('logo', 'logo_white');
          $table->string('logo_black')->nullable()->after('logo');
          $table->string('address')->nullable()->change();
          $table->string('city')->nullable()->change();
          $table->string('state')->nullable()->change();
          $table->string('country')->nullable()->change();
          $table->string('phone')->nullable()->change();
          $table->string('email')->nullable()->change();
          $table->string('website')->nullable()->change();
          $table->string('rut')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            //
        });
    }
};
