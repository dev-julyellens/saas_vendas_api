# Infraestrutura — API

A orquestração completa (API + SPA + Redis + filas + backup) está em:

**[`saas_vendas_web/infra/`](../../saas_vendas_web/infra/)**

Este repositório mantém:

- `docker-compose.yml` — desenvolvimento API-only (porta 8080)
- `Dockerfile` — imagem PHP 8.3 base
- `docker/nginx`, `docker/php`, `docker/postgres`
- Comando `php artisan health:check` — healthcheck Docker
- CI: `.github/workflows/ci.yml`

## Health check

```bash
php artisan health:check
# ou
curl http://localhost:8080/up
```

## Filas e scheduler

No stack unificado (`infra/`):

```bash
docker compose logs -f queue scheduler
```
