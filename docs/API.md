# API Reference

OpenITS exposes a small REST API for authentication status and token-based login. Most application functionality is accessed through the web UI. API routes are defined in `routes/api.php` and prefixed with `/api` by default.

**Base URL:** `{APP_URL}/api`

**Authentication:** Sanctum bearer tokens returned by login endpoints. Include the token in subsequent requests:

```http
Authorization: Bearer {token}
```

Token lifetime is configured via `SANCTUM_EXPIRATION` (minutes) in `.env`.

---

## Google OAuth

### GET `/api/auth/google/status`

Returns whether Google login is enabled and configured.

**Authentication:** None (public)

**Response** `200 OK`:

```json
{
  "enabled": true,
  "credentials_configured": true,
  "redirect_url": "https://example.com/auth/google"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `enabled` | boolean | Whether Google login is active |
| `credentials_configured` | boolean | Whether client ID, secret, and redirect URI are set |
| `redirect_url` | string\|null | OAuth redirect URL when enabled; `null` when disabled |

---

### POST `/api/auth/google/login`

Authenticate with a Google OAuth access token and receive a Sanctum API token.

**Authentication:** None (public; requires Google login to be enabled)

**Middleware:** `google.login.enabled` — returns **403** when Google login is disabled.

**Request body** (`application/json`):

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `access_token` | string | Yes | Valid Google OAuth access token |

**Example:**

```bash
curl -X POST https://example.com/api/auth/google/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"access_token": "ya29...."}'
```

**Response** `200 OK`:

```json
{
  "message": "Login successful.",
  "token": "1|abc...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@company.com",
    "avatar": "https://..."
  }
}
```

**Error responses:**

| Status | Condition |
|--------|-----------|
| `401` | Invalid or expired Google token |
| `422` | Missing email on Google account |
| `429` | Rate limit exceeded (brute-force protection) |
| `403` | Google login disabled |

---

## LDAP / Active Directory

### GET `/api/auth/ldap/status`

Returns whether LDAP login is enabled and configured.

**Authentication:** None (public)

**Response** `200 OK`:

```json
{
  "enabled": true,
  "credentials_configured": true,
  "domains": ["example.com"],
  "login_url": "https://example.com/auth/ldap/login"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `enabled` | boolean | Whether LDAP login is active |
| `credentials_configured` | boolean | Whether server, base DN, and domain are configured |
| `domains` | string[] | Allowed LDAP domains (empty when disabled) |
| `login_url` | string\|null | Web login endpoint when enabled |

---

### POST `/api/auth/ldap`

Authenticate with LDAP credentials and receive a Sanctum API token.

**Authentication:** None (public; requires LDAP login to be enabled)

**Middleware:** `ldap.login.enabled` — returns **403** when LDAP login is disabled.

**Request body** (`application/json` or form):

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `username` | string | Yes | LDAP username or UPN |
| `password` | string | Yes | LDAP password |
| `domain` | string | No | LDAP domain override |
| `remember` | boolean | No | Persist session (web clients) |

**Example:**

```bash
curl -X POST https://example.com/api/auth/ldap \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username": "jdoe", "password": "secret", "domain": "example.com"}'
```

**Response** `200 OK`:

```json
{
  "message": "Login successful.",
  "token": "2|xyz...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "jdoe@example.com"
  }
}
```

**Error responses:**

| Status | Condition |
|--------|-----------|
| `401` | Invalid credentials or user not provisioned |
| `422` | Validation error |
| `429` | Rate limit exceeded |
| `503` | LDAP server unreachable |
| `403` | LDAP login disabled |

---

## Web authentication routes

These routes are not under `/api` but are part of the external authentication interface:

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/auth/google` | Redirect to Google OAuth consent |
| `GET` | `/auth/google/callback` | OAuth callback handler |
| `POST` | `/auth/ldap/login` | Web LDAP login (session-based) |

See [README — Authentication](../README.md#authentication) for configuration.

---

## Rate limiting

All login endpoints share brute-force protection via `LoginThrottleService`. Limits are configured in `config/login.php`:

| Setting | Default | Description |
|---------|---------|-------------|
| `LOGIN_MAX_ATTEMPTS` | 5 | Failed attempts per credential + IP |
| `LOGIN_DECAY_MINUTES` | 1 | Lockout window |
| `LOGIN_IP_MAX_ATTEMPTS` | 20 | Failed attempts per IP |

When rate limited, endpoints return **429** with a throttle message.

---

## C4 JSON API

Authenticated C4 diagram endpoints return JSON for the interactive editor. These require a valid web session (cookie) or Sanctum token. Key patterns:

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/c4/systems/{system}/context` | Context diagram data |
| `GET` | `/c4/systems/{system}/containers` | Container diagram data |
| `GET` | `/c4/containers/{container}` | Component diagram data |
| `POST` | `/c4/systems/{system}/import` | Import C4 model |
| `GET` | `/c4/systems/{system}/export?format=json` | Export C4 model as JSON |

Full route listing: [README — Key routes](../README.md#key-routes).
