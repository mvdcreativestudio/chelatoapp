<?php

namespace App\Enums\MercadoPago;

enum MercadoPagoApplicationTypeEnum: string
{
    case PAID_ONLINE = 'paid_online'; // Pagos Online
    case PAID_PRESENCIAL = 'paid_presencial'; // Pagos Presenciales (POS)

    public static function getTranslateds(): array
    {
        return [
            self::PAID_ONLINE->value => 'Pagos Online',
            self::PAID_PRESENCIAL->value => 'Pagos Presenciales',
        ];
    }

    public function getDescription(): string
    {
        return self::getTranslateds()[$this->value] ?? 'Descripción no disponible';
    }
}
