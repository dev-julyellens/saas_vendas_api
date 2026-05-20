# API — Autenticação e Autorização

Base URL: `{APP_URL}/api/v1`

Todas as respostas seguem o envelope:

```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": {}
}
```

Erros:

```json
{
  "success": false,
  "message": "Descrição",
  "code": "ERROR_CODE",
  "errors": {}
}
```

## Autenticação (JWT)

Envie o token no header:

```
Authorization: Bearer {token}
```

| Config | Env | Padrão |
|--------|-----|--------|
| TTL access token | `JWT_TTL` | 60 min |
| Janela refresh | `JWT_REFRESH_TTL` | 20160 min (~14 dias) |
| Validar sessão (jti) | `AUTH_VALIDATE_SESSION` | true |
| Sessão única | `AUTH_SINGLE_SESSION` | false |
| Exigir e-mail verificado | `AUTH_REQUIRE_EMAIL_VERIFICATION` | false |
| Máx. tentativas login | `AUTH_MAX_LOGIN_ATTEMPTS` | 5 |
| Bloqueio (min) | `AUTH_LOCKOUT_MINUTES` | 15 |

---

## Endpoints públicos

### POST `/auth/login`

Autentica e retorna JWT.

**Throttle:** `auth-login` (10/min por IP + 5/min por e-mail)

**Body:**

```json
{
  "email": "admin@demo.com",
  "password": "password123"
}
```

**201/200 — sucesso:**

```json
{
  "success": true,
  "message": "Login realizado com sucesso.",
  "data": {
    "user": {
      "id": "uuid",
      "company_id": "uuid",
      "name": "Admin Demo",
      "email": "admin@demo.com",
      "is_master": false,
      "email_verified_at": null,
      "roles": [{ "slug": "empresa", "name": "Empresa" }]
    },
    "token": "eyJ...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

**422 — credenciais inválidas / conta bloqueada / e-mail não verificado**

---

### POST `/auth/forgot-password`

Solicita link de recuperação (resposta sempre genérica).

**Throttle:** `auth-password` (5/min)

**Body:**

```json
{ "email": "admin@demo.com" }
```

---

### POST `/auth/reset-password`

Redefine a senha com token recebido por e-mail.

**Body:**

```json
{
  "email": "admin@demo.com",
  "token": "token-do-email",
  "password": "NovaSenha@123",
  "password_confirmation": "NovaSenha@123"
}
```

---

### GET `/auth/email/verify/{id}/{hash}`

Confirma e-mail via link assinado (enviado por notificação).

**Query:** parâmetros de assinatura Laravel (`expires`, `signature`)

**Nome da rota:** `auth.verification.verify`

---

## Endpoints autenticados

**Middleware:** `auth.api` = JWT + sessão (jti) + conta ativa + e-mail verificado (se configurado)

### POST `/auth/logout`

Invalida o token atual e revoga a sessão.

**Throttle:** `auth-refresh`

---

### POST `/auth/refresh`

Renova o access token dentro da janela `JWT_REFRESH_TTL`.

**Resposta:**

```json
{
  "data": {
    "token": "eyJ...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

---

### GET `/auth/me`

Dados do usuário autenticado.

**Middleware adicional:** `tenant`, `tenant.company` (exceto Super Admin)

---

### POST `/auth/email/verification-notification`

Reenvia e-mail de confirmação.

---

### GET `/auth/sessions`

Lista sessões ativas (dispositivos).

---

### DELETE `/auth/sessions/{sessionId}`

Revoga uma sessão específica.

---

### DELETE `/auth/sessions`

Revoga todas as sessões do usuário.

---

## RBAC

### GET `/rbac/permissions`

Lista permissões do tenant.

**Permissão:** `roles.manage`

---

### GET `/rbac/roles`

Lista papéis do tenant.

---

### GET `/rbac/roles/{id}`

Detalhe do papel com permissões.

---

### PUT `/users/{userId}/roles`

Atribui papéis ao usuário.

**Permissão:** `users.manage`

**Body:**

```json
{ "role_ids": ["uuid-role-1", "uuid-role-2"] }
```

---

## Perfis (papéis)

| Perfil | Slug | Escopo |
|--------|------|--------|
| Super Admin | `is_master=true` | Plataforma inteira (Gate bypass) |
| Empresa | `empresa` | Administrador do tenant |
| Operacional | `operacional` | Operações diárias |
| Representante | `representante` | Carteira / consignação |
| Revendedor | `revendedor` | Vendas e consultas |

## Gates

| Gate | Descrição |
|------|-----------|
| `super-admin` | Usuário master |
| `empresa` | Papel empresa |
| `representante` | Papel representante |
| `revendedor` | Papel revendedor |
| `operacional` | Papel operacional |
| `manage-users` | Permissão `users.manage` |
| `manage-roles` | Permissão `roles.manage` |
| `view-audit` | Permissão `audit.view` |

`Gate::before` concede tudo ao Super Admin (`is_master`).

## Middleware

| Alias | Função |
|-------|--------|
| `auth.api` | JWT + sessão + ativo + e-mail verificado |
| `tenant` | Define `TenantContext` |
| `tenant.company` | Exige `company_id` (master isento) |
| `permission:{slug}` | RBAC por permissão |
| `role:{slug}` | RBAC por papel |
| `super-admin` | Apenas master |

## Credenciais seed

| Usuário | E-mail | Senha |
|---------|--------|-------|
| Super Admin | `master@saas.local` | `Master@123` |
| Admin Demo | `admin@demo.com` | `password123` |

## Logs de acesso

Eventos em `access_logs`: `login_success`, `login_failed`, `login_locked`, `logout`, `token_refreshed`, `password_reset_*`, `email_verified`, `session_revoked`.

## Segurança

- Blacklist JWT habilitada (logout invalida token)
- Proteção brute-force: throttle IP + e-mail + bloqueio de conta
- Isolamento multi-tenant via `company_id` + Global Scope
- Senha: mínimo 8 caracteres, maiúsculas e números (reset)
