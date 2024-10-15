<?php

use App\Enums\CurrentAccounts\StatusPaymentEnum;
use App\Enums\CurrentAccounts\TransactionTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('current_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('voucher');
            $table->decimal('total_debit', 10, 2);
            $table->date('transaction_date');
            $table->date('due_date')->nullable();
            $table->enum('status', array_map('ucfirst', array_column(StatusPaymentEnum::cases(), 'value')));
            $table->enum('transaction_type', array_map('ucfirst', array_column(TransactionTypeEnum::cases(), 'value')));
            $table->foreignId('client_id')->nullable()->constrained('clients')->onUpdate('restrict')->onDelete('restrict');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('restrict')->onDelete('restrict');
            $table->foreignId('currency_id')->constrained('currencies')->onUpdate('restrict')->onDelete('restrict');
            $table->foreignId('current_account_settings_id')->constrained('current_account_settings')->onUpdate('restrict')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_accounts');
    }
};
