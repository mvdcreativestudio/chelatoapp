<?php

namespace App\Enums\Expense;

enum ExpenseStatusEnum: string
{
    case PAID = 'Paid';
    case UNPAID = 'Unpaid';
    case PARTIAL = 'Partial';

    public static function getTranslateds(): array
    {
        return [
            self::PAID->value => 'Pagado',
            self::UNPAID->value => 'No pagado',
            self::PARTIAL->value => 'Parcialmente Pago',
        ];
    }
}