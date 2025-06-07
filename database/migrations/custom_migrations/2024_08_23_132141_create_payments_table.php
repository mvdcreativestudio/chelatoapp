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
        Schema::create('supplier_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices'); // FK a supplier_invoices
            $table->date('payment_date');
            $table->string('payment_method');
            $table->decimal('amount_paid', 15, 2); // Suposición de hasta 15 dígitos en total y 2 decimales
            $table->string('proof_of_payment', 255); // VARCHAR(255)
            $table->timestamps(); // Agrega columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_invoice_payments');
    }
};
