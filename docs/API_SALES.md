# API de Vendas

Base: `/api/v1/sales`  
Autenticação: Bearer JWT  
Permissões: `sales.view`, `sales.manage`

## Fluxo

1. **Criar** venda em rascunho (`draft`) com itens e desconto opcional.
2. **Confirmar** — baixa estoque, registra operação consignada (se aplicável), gera comissão.
3. **Cancelar** — apenas vendas ainda não confirmadas; cancela comissão pendente.

Vendas confirmadas não podem ser alteradas nem canceladas.

## Endpoints


| Método | Rota                  | Permissão | Descrição                  |
| ------ | --------------------- | --------- | -------------------------- |
| GET    | `/sales`              | view      | Lista paginada com filtros |
| POST   | `/sales`              | manage    | Cria venda (rascunho)      |
| GET    | `/sales/{id}`         | view      | Detalhe                    |
| PUT    | `/sales/{id}`         | manage    | Atualiza rascunho          |
| DELETE | `/sales/{id}`         | manage    | Exclui rascunho            |
| POST   | `/sales/{id}/confirm` | manage    | Confirma venda             |
| POST   | `/sales/{id}/cancel`  | manage    | Cancela rascunho           |
| GET    | `/sales/dashboard`    | view      | Resumo do período          |
| GET    | `/sales/report`       | view      | Relatório consolidado      |


## Criar venda

```http
POST /api/v1/sales
Content-Type: application/json

{
  "reseller_id": "uuid",
  "customer_id": null,
  "representative_id": "uuid",
  "consignment_id": null,
  "discount": 50,
  "items": [
    {
      "product_id": "uuid",
      "quantity": 2,
      "unit_price": 100,
      "consignment_item_id": null
    }
  ]
}
```

### Venda consignada

Informe `consignment_id` e, em cada item, `consignment_item_id`. O consignado deve estar **enviado** (`dispatched_at` preenchido).

## Filtros (GET `/sales`)

- `status`, `reseller_id`, `customer_id`, `representative_id`, `consignment_id`
- `code`, `date_from`, `date_to`, `min_total`, `max_total`
- `confirmed_only` (boolean)
- `per_page` (padrão 15)

## Dashboard e relatório

- `GET /sales/dashboard?date_from=2026-05-01&date_to=2026-05-31`
- `GET /sales/report` — mesmos filtros; inclui totais, por revendedor e top produtos.

## Regras de negócio

- **Subtotal**: soma `quantity × unit_price` (preço do produto se omitido).
- **Total**: `subtotal - discount` (desconto ≤ subtotal).
- **Estoque direto**: movimento `saida` na empresa.
- **Estoque consignado**: movimento `venda` no revendedor + `consignment_operations` (`venda_parcial`).
- **Comissão**: `total × representative.commission_rate` ao confirmar (status `pending`).

