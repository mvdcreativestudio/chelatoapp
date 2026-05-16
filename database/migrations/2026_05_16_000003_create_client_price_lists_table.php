<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('client_price_lists')) {
            Schema::create('client_price_lists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id');
                $table->unsignedBigInteger('price_list_id');
                $table->timestamps();

                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
                $table->foreign('price_list_id')->references('id')->on('price_lists')->onDelete('cascade');
                $table->unique(['client_id', 'price_list_id']);
            });
            return;
        }

        $indexName = 'client_price_lists_client_id_price_list_id_unique';
        $exists = collect(DB::select("SHOW INDEX FROM `client_price_lists` WHERE Key_name = ?", [$indexName]))->isNotEmpty();
        if (!$exists) {
            DB::statement("
                DELETE c1 FROM client_price_lists c1
                INNER JOIN client_price_lists c2
                  ON c1.client_id = c2.client_id
                 AND c1.price_list_id = c2.price_list_id
                 AND c1.id > c2.id
            ");
            Schema::table('client_price_lists', function (Blueprint $table) {
                $table->unique(['client_id', 'price_list_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_price_lists');
    }
};
