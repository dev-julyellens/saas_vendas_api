<?php

declare(strict_types=1);

namespace App\Core\Enums;

enum FinancialTransactionType: string
{
    case Receivable = 'receivable';
    case Payable = 'payable';
    case Income = 'income';
    case Expense = 'expense';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
