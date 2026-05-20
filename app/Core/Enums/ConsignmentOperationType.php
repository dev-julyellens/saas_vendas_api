<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * Operações rastreáveis do consignado — cada uma gera stock_movements.
 */
enum ConsignmentOperationType: string
{
    case Envio = 'envio';
    case VendaParcial = 'venda_parcial';
    case DevolucaoParcial = 'devolucao_parcial';
    case Coleta = 'coleta';
    case Fechamento = 'fechamento';
    case Divergencia = 'divergencia';
    case Perda = 'perda';
    case Avaria = 'avaria';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
