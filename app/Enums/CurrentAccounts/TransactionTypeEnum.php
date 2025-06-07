<?php

namespace App\Enums\CurrentAccounts;

enum TransactionTypeEnum: string {
    case SALE = 'Sale';
    case PURCHASE = 'Purchase';

    public static function getTranslateds(): array
    {
        return [
            self::SALE->value => 'Venta a crédito',
            self::PURCHASE->value => 'Compra a crédito',
        ];
    }

    public static function getValues(): array
    {
        return [
            self::SALE->value,
            self::PURCHASE->value,
        ];
    }
}
