<?php

use App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mercadopago_accounts', function (Blueprint $table) {
            $table->enum('type', array_column(MercadoPagoApplicationTypeEnum::cases(), 'value'))
                ->after('secret_key')
                ->default(MercadoPagoApplicationTypeEnum::PAID_ONLINE->value);
        });

        DB::table('mercadopago_accounts')->update(['type' => MercadoPagoApplicationTypeEnum::PAID_ONLINE->value]);
    }

    public function down(): void
    {
        Schema::table('mercadopago_accounts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
