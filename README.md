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

## Requisitos

- [Docker](https://www.docker.com/) e Docker Compose
- (Opcional) Front-end em `saas_vendas_web` — Vite na porta **5173**

> O projeto exige **PHP ≥ 8.3**. O XAMPP com PHP 8.2 **não** executa `php artisan` localmente; use os comandos **dentro do container** (veja abaixo).

## Início rápido (Docker)

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app git config --global --add safe.directory /var/www/html
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

| Recurso | URL |
|---------|-----|
| API (nginx) | `http://localhost:8080/api/v1` |
| Health check | `http://localhost:8080/up` |

Login demo: `admin@demo.com` / `password123`

**Não use** `php artisan serve` com Docker — a API já é servida pelo **nginx** na porta **8080**. A porta **8000** não está exposta no host.

## PostgreSQL e DBeaver

| Variável | Valor típico | Uso |
|----------|--------------|-----|
| `DB_PORT` | `5432` | Laravel **dentro** do Docker → host `postgres:5432` |
| `DB_PUBLISH_PORT` | `5433` | Acesso do **Windows** (DBeaver) → `localhost:5433` |

Se você já tem Postgres local na porta **5432**, mantenha `DB_PUBLISH_PORT=5433` no `.env` e reinicie o stack (`docker compose down && docker compose up -d`).

**DBeaver (banco do projeto):** host `localhost`, porta `DB_PUBLISH_PORT` (ex.: 5433), database `saas_vendas`, usuário `saas`, senha `secret`.

> Não altere `DB_PORT` para 5433 no `.env` — isso quebra o `artisan migrate` no container.

## Integração com o front-end

O SPA (Vite) roda em `http://localhost:5173`. Configure no `.env` do front (`saas_vendas_web`):

```env
VITE_API_BASE_URL=http://localhost:8080/api/v1
```

No `.env` da API, alinhe:

```env
APP_URL=http://localhost:8080
FRONTEND_URL=http://localhost:5173
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

Login: `POST /api/v1/auth/login` com body JSON:

```json
{ "email": "admin@demo.com", "password": "password123" }
```

A senha deve ter **no mínimo 8 caracteres** (validação da API).

## Comandos Artisan (sempre no container)

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan test
docker compose exec app composer update
```

## Testes

```bash
docker compose exec app php artisan test
```

Os testes usam o ambiente configurado em `phpunit.xml`. Com Docker, o PHP 8.3 do container atende ao requisito do `composer.json`.

## Documentação

- [Arquitetura](docs/ARCHITECTURE.md)
- [Autenticação e autorização (API)](docs/API_AUTH.md)
- [Consignado (API)](docs/API_CONSIGNMENT.md)
- [Vendas (API)](docs/API_SALES.md)

## Estrutura de pastas

```
app/
├── Core/                 # Kernel compartilhado (tenant, base classes, middleware)
├── Models/               # User (autenticação)
└── Modules/              # Bounded contexts por domínio
    ├── Auth/
    ├── Company/
    ├── Product/
    ├── Sale/
    └── …
```

## Solução de problemas

### `platform_check.php` — PHP 8.2 no Windows

O `php` do XAMPP (8.2) não atende ao projeto. Use `docker compose exec app php artisan …` ou instale PHP 8.3+ no host.

### CORS com status `(null)` no navegador

Geralmente a requisição **não chegou** à API (URL/porta errada). Confirme `http://localhost:8080`, não `8000`. Teste:

```bash
curl -X POST http://localhost:8080/api/v1/auth/login ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\":\"admin@demo.com\",\"password\":\"password123\"}"
```

### HTTP 422 no login

Validação falhou: envie `email` e `password` (mín. 8 caracteres). Rode `migrate --seed` se o usuário demo não existir no PostgreSQL.

### `dubious ownership` no Git (Composer no container)

```bash
docker compose exec app git config --global --add safe.directory /var/www/html
```

### Lock file desatualizado

```bash
docker compose exec app composer update
```

### `Connection refused` em `postgres:5433`

`DB_PORT` deve ser **5432** (porta interna). Use `DB_PUBLISH_PORT` apenas para o DBeaver no host.

## Desenvolvimento sem Docker (avançado)

Requer PHP 8.3+, PostgreSQL e Redis locais. Copie `.env.example`, ajuste `DB_HOST=127.0.0.1` e `REDIS_HOST=127.0.0.1`, depois `composer install` e `php artisan migrate --seed`.
