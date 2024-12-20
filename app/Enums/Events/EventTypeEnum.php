<?php
namespace App\Enums\Events;

use App\Traits\EnumHelper;

enum EventTypeEnum: string
{
    use EnumHelper;
    // case PRODUCTS = 'products';
    // case INVOICES = 'invoices';
    // case ORDERS = 'orders';
    // case USERS = 'users';
    case ECCOMMERCE = 'eccommerce';

    public static function getTranslateds(): array
    {
        return [
            // self::PRODUCTS->value => 'Productos',
            // self::INVOICES->value => 'Facturación',
            // self::ORDERS->value => 'Ventas',
            // self::USERS->value => 'Usuarios',
            self::ECCOMMERCE->value => 'E-commerce',
        ];
    }

    public function getDescription(): string
    {
        return self::getTranslateds()[$this->value] ?? 'Descripción no disponible';
    }
}
