<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->double('discount')->nullable()->default(null)->change();
            $table->string('image')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->double('discount')->nullable(false)->default(0)->change();
            $table->string('image')->nullable(false)->default('')->change();
        });
    }
};

