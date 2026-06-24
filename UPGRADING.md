# Upgrading OpenITS

This guide describes how to upgrade a self-hosted OpenITS installation to a newer version.

## Supported versions

| Policy | Details |
|--------|---------|
| **Actively maintained** | Latest release on `main` and the current Git tag (see [Releases](https://github.com/imRezaAlie/openits/releases)) |
| **Security fixes** | Provided for latest `main` only — see [SECURITY.md](SECURITY.md#supported-versions) |
| **Older versions** | Not maintained separately; upgrade to the latest release |

## Before you upgrade

1. **Back up your database** (MySQL dump or copy SQLite file).
2. **Back up `.env`** and any uploaded files under `storage/`.
3. **Read the release notes** for your target version on [GitHub Releases](https://github.com/imRezaAlie/openits/releases).
4. Put the application in **maintenance mode** during the upgrade (optional but recommended):

```bash
php artisan down
```

## Standard upgrade procedure

From your OpenITS installation directory:

```bash
# 1. Fetch the new version
git fetch origin
git checkout main          # or a specific tag, e.g. git checkout v1.1.0
git pull origin main

# 2. Update PHP dependencies
composer install --no-dev --optimize-autoloader   # production
# composer install                              # development

# 3. Update frontend assets (if you build assets locally)
npm ci && npm run build

# 4. Apply database migrations
php artisan migrate --force

# 5. Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart queue workers (if used)
php artisan queue:restart

# 7. Bring the site back up
php artisan up
```

### Verify after upgrade

```bash
php artisan --version
php artisan migrate:status
composer test    # optional, in development/staging
```

Sign in to the web UI and confirm dashboards, C4 diagrams, and API documentation load correctly.

## Version-specific notes

### Upgrading to v1.1.0

Release notes: https://github.com/imRezaAlie/openits/releases/tag/v1.1.0

| Area | Change |
|------|--------|
| Documentation | Added CONTRIBUTING.md, SECURITY.md, GOVERNANCE.md, ROADMAP.md, docs/ |
| Tooling | Added Laravel Pint (`composer lint`); run `composer lint:fix` only in development |
| Database | No breaking schema changes beyond existing migrations — run `php artisan migrate` |
| Configuration | Review `.env.example` for new variables; merge into your `.env` manually |
| Interfaces | No breaking changes to public REST auth endpoints documented in [docs/API.md](docs/API.md) |

## Configuration changes

When upgrading, compare your `.env` with [.env.example](.env.example). Common settings to review:

| Variable | Purpose |
|----------|---------|
| `APP_DEBUG` | Must be `false` in production |
| `REGISTRATION_ENABLED` | Self-service sign-up (default `false`) |
| `SESSION_ENCRYPT` / `SESSION_SECURE_COOKIE` | Production session hardening |
| `LDAP_*` / `GOOGLE_*` | Authentication providers |
| `SANCTUM_EXPIRATION` | API token lifetime |

Do not overwrite your existing `.env` with `.env.example` — merge new keys only.

## Queue workers and background jobs

If you use C4 imports or LDAP sync with `QUEUE_CONNECTION=database` or `redis`:

```bash
php artisan queue:restart
# Ensure a worker is running, e.g.:
php artisan queue:work --tries=3
```

## Rollback

If an upgrade fails:

1. `php artisan down`
2. `git checkout <previous-tag-or-commit>`
3. `composer install`
4. Restore database backup if migrations were applied
5. `php artisan config:clear && php artisan cache:clear`
6. `php artisan up`

Database rollbacks are not automated — restore from backup if `php artisan migrate` cannot be reversed safely.

## Getting help

- **Bugs:** [GitHub Issues](https://github.com/imRezaAlie/openits/issues/new/choose)
- **Security:** [SECURITY.md](SECURITY.md) (do not use public issues for vulnerabilities)
- **Releases:** [GitHub Releases](https://github.com/imRezaAlie/openits/releases)
