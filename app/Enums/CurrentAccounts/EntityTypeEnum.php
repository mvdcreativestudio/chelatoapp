<?php

namespace App\Enums\CurrentAccounts;

enum EntityTypeEnum: string {
    case CLIENT = 'client';
    case SUPPLIER = 'supplier';

    public static function getTranslateds(): array
    {
        return [
            self::CLIENT->value => 'Cliente',
            self::SUPPLIER->value => 'Proveedor',
        ];
    }
}
