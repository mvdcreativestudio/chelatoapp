<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosOrders extends Migration
{
    public function up()
    {
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('hour');
            $table->foreignId('cash_register_log_id')->constrained()->onDelete('cascade');
            $table->integer('cash_sales')->default(0);
            $table->integer('pos_sales')->default(0);
            $table->integer('discount')->default(0);
            $table->string('client_type');
            $table->json('products'); 
            $table->integer('subtotal')->default(0); 
            $table->integer('total')->default(0); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_orders');
    }
}
