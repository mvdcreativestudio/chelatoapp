<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_categories')) {
            return;
        }

        if (Schema::hasColumn('product_categories', 'parent_id')) {
            return;
        }

        $afterColumn = Schema::hasColumn('product_categories', 'image_url') ? 'image_url' : null;

        Schema::table('product_categories', function (Blueprint $table) use ($afterColumn) {
            if ($afterColumn) {
                $table->foreignId('parent_id')->nullable()->after($afterColumn)->constrained('product_categories');
            } else {
                $table->foreignId('parent_id')->nullable()->constrained('product_categories');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_categories')) {
            return;
        }

        if (! Schema::hasColumn('product_categories', 'parent_id')) {
            return;
        }

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
};
