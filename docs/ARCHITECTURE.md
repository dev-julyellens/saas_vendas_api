# Arquitetura — SaaS Vendas Consignadas

## Visão geral

Sistema **API ONLY** (sem Blade/Livewire) com **multi-tenant lógico** via coluna `company_id` em todas as entidades de negócio. Um único banco PostgreSQL isola dados por tenant usando **Global Scope** + **TenantContext** request-scoped.

## Decisões arquiteturais

### 1. Multi-tenant lógico (single database)

- **Por quê:** menor custo operacional que schema/database por tenant; migrations únicas; escalonamento horizontal da API.
- **Como:** `TenantContext` define `company_id` após JWT; `CompanyScope` filtra queries automaticamente.
- **Risco mitigado:** nunca confiar apenas no client — scope + policies + FK `company_id`.

### 2. Estrutura modular (`app/Modules`)

- Cada domínio (Product, Sale, Financial…) é um **bounded context** com Provider próprio.
- Rotas versionadas em `/api/v1/*`.
- Permite times trabalharem em módulos isolados e extrair microsserviços no futuro.

### 3. Camadas (Clean Architecture pragmática)

```
HTTP (Controller, FormRequest, Resource)
    → Service (regras de negócio, transações)
        → Repository (persistência)
            → Model (Eloquent + scopes)
```

- **DTOs:** contratos imutáveis entre camadas.
- **Policies + middleware `permission`:** RBAC em dois níveis (recurso + slug).

### 4. JWT (stateless)

- Guard `api` com driver `jwt` — sem sessão server-side.
- Claims incluem `company_id` para validação rápida.
- Refresh e blacklist via `php-open-source-saver/jwt-auth`.

### 5. Auditoria assíncrona

- Trait `Auditable` dispara `ModelAudited`.
- Listener `PersistAuditLog` enfileirado — não aumenta latência de escrita.
- Canal de log dedicado `audit` para correlação.

### 6. Redis

- **Cache:** `CACHE_STORE=redis`
- **Filas:** `QUEUE_CONNECTION=redis` + container `queue` no Docker
- **Rate limit:** throttle API e login

### 7. Soft deletes + UUID

- UUID como PK: integrações externas e segurança contra enumeração.
- Soft delete em entidades principais (vendas, produtos, comissões).

### 8. Módulo Product como referência

Implementa o padrão completo: Repository, Service, DTO, Form Requests, Policy, Events, Listeners, Jobs, Notifications.

Demais módulos possuem Models, Migrations e rotas placeholder para expansão incremental.

## RBAC

| Tabela | Escopo |
|--------|--------|
| `permissions` | Global (seed) |
| `roles` | Por `company_id` |
| `role_user` | Usuário ↔ papel |

Permissões exemplo: `products.manage`, `sales.manage`.

## Endpoints principais

| Método | Rota | Auth |
|--------|------|------|
| POST | `/api/v1/auth/login` | Público |
| GET | `/api/v1/auth/me` | JWT + tenant |
| CRUD | `/api/v1/products` | JWT + tenant + permission |

## Próximos passos sugeridos

1. Implementar controllers nos módulos restantes seguindo `Product`.
2. Regras de comissão automática no evento `SaleCompleted`.
3. Conciliação financeira ligada a vendas/devoluções.
4. Testes de isolamento multi-tenant (tenant A não acessa dados de B).
