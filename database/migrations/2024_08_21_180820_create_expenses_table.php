<?php

use App\Enums\Expense\ExpenseStatusEnum;
use App\Enums\Expense\ExpenseTemporalStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->enum('status', array_map('ucfirst', array_column(ExpenseStatusEnum::cases(), 'value')));
            $table->date('due_date');
            $table->enum('temporal_status', array_map('ucfirst', array_column(ExpenseTemporalStatusEnum::cases(), 'value')))->nullable();
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->foreignId('expense_category_id')->constrained()->onDelete('restrict');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
