<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('product_categories', 'slug')) {
                $table->string('slug')->nullable()->after('name'); // primero nullable
            }
        });

        // Llenar slugs vacíos o duplicados con un valor único temporal
        $categories = DB::table('product_categories')->get();
        foreach ($categories as $category) {
            $slug = \Str::slug($category->name);
            $originalSlug = $slug;
            $i = 1;

            // Asegurarse de que el slug sea único
            while (
                DB::table('product_categories')
                    ->where('slug', $slug)
                    ->where('id', '!=', $category->id)
                    ->exists()
            ) {
                $slug = $originalSlug . '-' . $i++;
            }

            DB::table('product_categories')
                ->where('id', $category->id)
                ->update(['slug' => $slug]);
        }

        // Agregar el índice único solo si aún no existe
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes('product_categories');
        if (!array_key_exists('product_categories_slug_unique', $indexes)) {
            Schema::table('product_categories', function (Blueprint $table) {
                $table->unique('slug', 'product_categories_slug_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            if (Schema::hasColumn('product_categories', 'slug')) {
                $table->dropUnique('product_categories_slug_unique');
                $table->dropColumn('slug');
            }
        });
    }
};
