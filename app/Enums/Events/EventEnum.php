<?php

namespace App\Enums\Events;

use App\Enums\Events\EventTypeEnum;

enum EventEnum: string
{
    case LOW_STOCK = 'low_stock';
    case OUT_OF_STOCK = 'out_of_stock';
    case NEW_INVOICE = 'new_invoice';
    case ORDER_MAX_10 = 'order_max_10';
    case NEW_USER = 'new_user';
    public static function getTranslateds(): array
    {
        return [
            self::LOW_STOCK->value => 'Stock por debajo del margen de seguridad',
            self::OUT_OF_STOCK->value => 'Producto sin stock',
            self::NEW_INVOICE->value => 'Nueva factura emitida',
            self::ORDER_MAX_10->value => 'Más de 10 pedidos en un día',
            self::NEW_USER->value => 'Nuevo usuario registrado',
        ];
    }

    public static function getAssociatedTypeEvents(): array
    {
        return [
            EventTypeEnum::PRODUCTS->value => [
                self::LOW_STOCK->value,
                self::OUT_OF_STOCK->value,
            ],
            EventTypeEnum::INVOICES->value => [
                self::NEW_INVOICE->value,
            ],
            EventTypeEnum::ORDERS->value => [
                self::ORDER_MAX_10->value,
            ],
            EventTypeEnum::USERS->value => [
                self::NEW_USER->value,
            ],
        ];
    }

    public function getDescription(): string
    {
        return self::getTranslateds()[$this->value] ?? 'Descripción no disponible';
    }
}
