# API — Consignado

Base: `POST /api/v1/consignments` (auth + tenant + `consignment.manage` / `consignment.view`)

## Fluxo

1. `POST /consignments` — cadastra itens (sem movimentar estoque)
2. `POST /consignments/{id}/dispatch` — **envio** (saída empresa + consignado no revendedor)
3. `POST .../partial-sale` — venda parcial (`venda` no revendedor)
4. `POST .../partial-return` — devolução parcial
5. `POST .../collect` — coleta do representante
6. `POST .../close` — fechamento (retorna pendente + status `fechado`)

Operações adicionais: `loss`, `damage`, `divergence`

## Status

| Status | Descrição |
|--------|-----------|
| `aberto` | Enviado, em andamento |
| `parcial` | Houve venda/devolução/perda/etc. |
| `atrasado` | Passou `expected_return_at` |
| `fechado` | Encerrado |

## Rastreabilidade

- `GET /consignments/{id}/operations` — log de operações
- `GET /consignments/{id}/movements` — `stock_movements` vinculados
- Auditoria Eloquent em `consignments` / `consignment_items`
- Canal `audit` para eventos de domínio

## Regras

- Saldo **somente** via `stock_movements` (`StockMovementService`)
- Toda operação gera registro em `consignment_operations` + movimentos
- Fechamento bloqueado se já `fechado`
- Envio exige estoque na empresa
