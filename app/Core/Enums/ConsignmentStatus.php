<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * Ciclo de vida do consignado.
 */
enum ConsignmentStatus: string
{
    case Aberto = 'aberto';
    case Parcial = 'parcial';
    case Fechado = 'fechado';
    case Atrasado = 'atrasado';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Aberto, self::Parcial, self::Atrasado], true);
    }
}
