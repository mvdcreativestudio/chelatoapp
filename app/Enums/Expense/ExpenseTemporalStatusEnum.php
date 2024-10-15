<?php

namespace App\Enums\Expense;

enum ExpenseTemporalStatusEnum: string
{
    case ON_TIME = 'On Time';
    case DUE_SOON = 'Due Soon';
    case OVERDUE = 'Overdue';
    case DUE_TODAY = 'Due Today';
    public static function getTranslateds(): array
    {
        return [
            self::ON_TIME->value => 'A tiempo',
            self::DUE_SOON->value => 'Pronto vencimiento',
            self::OVERDUE->value => 'Vencido',
            self::DUE_TODAY->value => 'Vence hoy',
        ];
    }
}
