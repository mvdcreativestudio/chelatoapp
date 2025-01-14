<?php

namespace App\Enums\Events;

use App\Enums\Events\EventTypeEnum;
use App\Traits\EnumHelper;

enum EventEnum: string
{
    use EnumHelper;

    // case LOW_STOCK = 'low_stock';
    // case OUT_OF_STOCK = 'out_of_stock';
    // case NEW_INVOICE = 'new_invoice';
    // case ORDER_MAX_10 = 'order_max_10';
    // case NEW_USER = 'new_user';
    // CASE NEW_ORDER_ECCOMMERCE = 'new_order_eccommerce';
    case NEW_ORDER_ADMIN_NOTIFICATION_ECCOMERCE = 'new_order_admin_notification_eccommerce';
    case NEW_ORDER_CUSTOMER_CONFIRMATION_ECCOMERCE = 'new_order_customer_confirmation_eccommerce';
    public static function getTranslateds(): array
    {
        return [
            // self::LOW_STOCK->value => 'Stock por debajo del margen de seguridad',
            // self::OUT_OF_STOCK->value => 'Producto sin stock',
            // self::NEW_INVOICE->value => 'Nueva factura emitida',
            // self::ORDER_MAX_10->value => 'Más de 10 pedidos en un día',
            // self::NEW_USER->value => 'Nuevo usuario registrado',

            // self::NEW_ORDER_ECCOMMERCE->value => 'Nuevo pedido en la tienda online',
            self::NEW_ORDER_ADMIN_NOTIFICATION_ECCOMERCE->value => 'Nuevo pedido en la tienda online (Notificación para administradores)',
            self::NEW_ORDER_CUSTOMER_CONFIRMATION_ECCOMERCE->value => 'Nuevo pedido en la tienda online (Confirmación para el cliente)',
        ];
    }

    public static function getAssociatedTypeEvents(): array
    {
        return [
            // EventTypeEnum::PRODUCTS->value => [
            //     self::LOW_STOCK->value,
            //     self::OUT_OF_STOCK->value,
            // ],
            // EventTypeEnum::INVOICES->value => [
            //     self::NEW_INVOICE->value,
            // ],
            // EventTypeEnum::ORDERS->value => [
            //     self::ORDER_MAX_10->value,
            // ],
            // EventTypeEnum::USERS->value => [
            //     self::NEW_USER->value,
            // ],
            EventTypeEnum::ECCOMMERCE->value => [
                self::NEW_ORDER_ADMIN_NOTIFICATION_ECCOMERCE->value,
                self::NEW_ORDER_CUSTOMER_CONFIRMATION_ECCOMERCE->value,
            ],
        ];
    }

    public function getDescription(): string
    {
        return self::getTranslateds()[$this->value] ?? 'Descripción no disponible';
    }
}
