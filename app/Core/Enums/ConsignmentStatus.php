<?php

declare(strict_types=1);

namespace App\Core\Enums;

enum ConsignmentStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case PartialReturn = 'partial_return';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
