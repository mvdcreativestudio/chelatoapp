<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->integer('type'); // 101 for eTicket, 111 for eFactura
            $table->string('serie');
            $table->integer('nro');
            $table->string('caeNumber');
            $table->json('caeRange');
            $table->date('caeExpirationDate');
            $table->decimal('total', 10, 2);
            $table->dateTime('emitionDate');
            $table->string('sentXmlHash');
            $table->string('securityCode');
            $table->string('qrUrl');
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
        Schema::dropIfExists('receipts');
    }
}
