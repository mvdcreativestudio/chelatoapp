<?php
namespace App\Enums\Events;

enum EventTypeEnum: string
{
    case PRODUCTS = 'products';
    case INVOICES = 'invoices';
    case ORDERS = 'orders';
    case USERS = 'users';

    public static function getTranslateds(): array
    {
        return [
            self::PRODUCTS->value => 'Productos',
            self::INVOICES->value => 'Facturación',
            self::ORDERS->value => 'Ventas',
            self::USERS->value => 'Usuarios',
        ];
    }

    public function getDescription(): string
    {
        return self::getTranslateds()[$this->value] ?? 'Descripción no disponible';
    }
}
