# SaaS Vendas Consignadas

API REST multi-tenant para gestão de vendas consignadas (Laravel 12, PostgreSQL, Redis, JWT).

## Stack

| Tecnologia | Uso |
|------------|-----|
| Laravel 12 | Framework API ONLY |
| PHP 8.3+ | Runtime (Docker) |
| PostgreSQL 16 | Banco principal |
| Redis 7 | Cache, filas, rate limit |
| JWT | Autenticação stateless |

## Início rápido (Docker)

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

API: `http://localhost:8080/api`

Login demo: `admin@demo.com` / `password123`

## Documentação

- [Arquitetura](docs/ARCHITECTURE.md)
- [Autenticação e autorização (API)](docs/API_AUTH.md)
- [Consignado (API)](docs/API_CONSIGNMENT.md)

## Estrutura de pastas

```
app/
├── Core/                 # Kernel compartilhado (tenant, base classes, middleware)
├── Models/               # User (autenticação)
└── Modules/              # Bounded contexts por domínio
    ├── Auth/
    ├── Company/
    ├── Product/          # Referência completa (DTO, Policy, Events…)
    ├── Sale/
    └── …
```

## Testes

```bash
php artisan test
```
