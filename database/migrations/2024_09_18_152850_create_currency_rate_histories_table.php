<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyRateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
    */
    public function up()
    {
        Schema::create('currency_rate_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_rate_id')->constrained('currency_rates')->onDelete('cascade');
            $table->date('date');
            $table->decimal('buy', 10, 5)->nullable();
            $table->decimal('sell', 10, 5)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
    */
    public function down()
    {
        Schema::dropIfExists('currency_rate_histories');
    }
}
