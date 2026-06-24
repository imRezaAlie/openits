# CLI Reference

OpenITS provides Artisan console commands for LDAP administration and Google login management. Run any command with `--help` for built-in Laravel help.

**Invocation:**

```bash
php artisan {command} [options]
```

---

## LDAP commands

### `ldap:test`

Test connectivity to the configured LDAP server.

| Option | Description |
|--------|-------------|
| *(none)* | Uses settings from database/environment |

**Exit codes:** `0` success, `1` failure (credentials missing or connection failed)

```bash
php artisan ldap:test
```

---

### `ldap:status`

Display LDAP login enabled state and connection settings.

| Option | Description |
|--------|-------------|
| *(none)* | Prints a table of current settings |

```bash
php artisan ldap:status
```

**Output fields:** LDAP login enabled, credentials configured, server, port, base DN, domain, SSL, STARTTLS.

---

### `ldap:enable`

Enable LDAP login. Requires LDAP credentials to be configured.

| Option | Description |
|--------|-------------|
| *(none)* | Enables LDAP and persists to settings |

```bash
php artisan ldap:enable
```

---

### `ldap:disable`

Disable LDAP login.

```bash
php artisan ldap:disable
```

---

### `ldap:sync`

Synchronize all LDAP directory users into the local database.

| Option | Description |
|--------|-------------|
| `--queue` | Dispatch sync as a background queue job instead of running synchronously |

```bash
php artisan ldap:sync           # synchronous
php artisan ldap:sync --queue   # background job
```

**Exit codes:** `0` success, `1` failure (credentials missing or sync error)

**Notes:** Requires a queue worker (`php artisan queue:work`) when using `--queue`.

---

## Google login commands

### `google:login:status`

Check or update Google OAuth login status.

| Option | Description |
|--------|-------------|
| `--enable` | Enable Google login (requires OAuth credentials in `.env`) |
| `--disable` | Disable Google login |

Only one of `--enable` or `--disable` may be used per invocation.

```bash
php artisan google:login:status              # show status table
php artisan google:login:status --enable   # enable
php artisan google:login:status --disable  # disable
```

**Output fields:** Google login enabled, credentials configured, client ID present, redirect URI.

---

## Standard Laravel commands

OpenITS also uses common Laravel Artisan commands documented in the [Laravel docs](https://laravel.com/docs/12.x/artisan):

| Command | Purpose |
|---------|---------|
| `php artisan migrate` | Run database migrations |
| `php artisan db:seed` | Seed demo data |
| `php artisan serve` | Start development server |
| `php artisan queue:work` | Process background jobs (C4 imports, LDAP sync) |
| `php artisan test` | Run the PHPUnit test suite |
| `php artisan key:generate` | Generate `APP_KEY` |

---

## Environment prerequisites

| Command group | Requirements |
|---------------|--------------|
| LDAP | PHP `ldap` extension; `LDAP_*` variables in `.env` |
| Google | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` in `.env` |
| Queue | `QUEUE_CONNECTION` configured; worker running for async jobs |

See [README — Authentication](../README.md#authentication) for full environment variable reference.
