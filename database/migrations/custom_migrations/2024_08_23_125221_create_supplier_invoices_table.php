<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders'); 
            $table->string('invoice_number');
            $table->string('invoice_type');
            $table->string('currency');
            $table->decimal('exchange_rate', 10, 2);
            $table->date('due_date');
            $table->decimal('total_amount', 15, 2); 
            $table->string('cfe_id')->nullable(); 
            $table->enum('payment_status', ['0', '1', '2']); 
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
        Schema::dropIfExists('supplier_invoices');
    }
};
