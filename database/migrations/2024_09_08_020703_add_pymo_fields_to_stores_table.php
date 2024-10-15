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
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'invoices_enabled')) {
                $table->boolean('invoices_enabled')->default(false); // Campo para habilitar o no la facturación
            }
            if (!Schema::hasColumn('stores', 'pymo_user')) {
                $table->string('pymo_user')->nullable(); // Usuario para PyMo
            }
            if (!Schema::hasColumn('stores', 'pymo_password')) {
                $table->string('pymo_password')->nullable(); // Contraseña para PyMo
            }
            if (!Schema::hasColumn('stores', 'pymo_branch_office')) {
                $table->string('pymo_branch_office')->nullable(); // Sucursal para PyMo
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'invoices_enabled')) {
                $table->dropColumn('invoices_enabled');
            }
            if (Schema::hasColumn('stores', 'pymo_user')) {
                $table->dropColumn('pymo_user');
            }
            if (Schema::hasColumn('stores', 'pymo_password')) {
                $table->dropColumn('pymo_password');
            }
            if (Schema::hasColumn('stores', 'pymo_branch_office')) {
                $table->dropColumn('pymo_branch_office');
            }
        });
    }
};
