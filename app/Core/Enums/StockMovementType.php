<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * Tipos de movimentação de estoque.
 * Saldo = SUM(quantity * direction) — nunca coluna de saldo fixo.
 */
enum StockMovementType: string
{
    case Entrada = 'entrada';
    case Saida = 'saida';
    case Consignado = 'consignado';
    case Devolucao = 'devolucao';
    case Venda = 'venda';
    case Perda = 'perda';
    case Avaria = 'avaria';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Movimentos que aumentam o saldo no contexto (empresa ou revendedor). */
    public function isInbound(): bool
    {
        return match ($this)
        {
            self::Entrada, self::Devolucao => true,
            default => false,
        };
    }

    /** Quantidade com sinal para agregação de saldo (+ entrada, − saída). */
    public function signedQuantity(int $quantity): int
    {
        return $this->isInbound() ? abs($quantity) : -abs($quantity);
    }
}
