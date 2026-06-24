# Security Policy

## Supported versions

Security fixes are provided for the latest release on the `main` branch. Older versions may not receive patches.

| Version | Supported |
|---------|-----------|
| Latest `main` | Yes |

## Reporting a vulnerability

**Please do not report security vulnerabilities through public GitHub Issues.**

If you discover a security issue in OpenITS, report it privately:

1. **Email:** rezaalie70@gmail.com (replace `[at]` with `@` if copying from rendered docs)
2. **Subject line:** `[OpenITS Security]` followed by a brief description
3. **Include:**
   - Description of the vulnerability and potential impact
   - Steps to reproduce
   - Affected version or commit hash
   - Any proof-of-concept code (if available)
   - Your contact information for follow-up

You may also use [GitHub Private Vulnerability Reporting](https://github.com/imRezaAlie/openits/security/advisories/new) if enabled on the repository.

## Vulnerability response process

This is the maintainer workflow when a private security report is received:

| Step | Action |
|------|--------|
| **1. Receive** | Reports arrive via email (`rezaalie70@gmail.com`) or [GitHub private vulnerability reporting](https://github.com/imRezaAlie/openits/security/advisories/new). Public GitHub Issues are **not** used for security bugs. |
| **2. Acknowledge** | A maintainer confirms receipt to the reporter and requests any missing details (reproduction steps, affected version, impact). |
| **3. Triage** | Validate the report, assign severity (critical / high / medium / low), and decide whether a hotfix or scheduled release is needed. |
| **4. Fix** | Develop and test a patch on `main` (or a private branch for embargoed issues). Security fixes target the latest supported version — see [Supported versions](#supported-versions). |
| **5. Disclose** | Coordinate timing with the reporter. Prefer shipping a fix before public disclosure when possible. Publish a [GitHub Security Advisory](https://github.com/imRezaAlie/openits/security/advisories) and release notes when appropriate. |
| **6. Credit** | Thank the reporter in the advisory and release notes unless they request anonymity — see [Credit for security reporters](#credit-for-security-reporters). |

### Response timelines

| Stage | Target |
|-------|--------|
| Initial acknowledgment | Within **48 hours** |
| Status update | Within **7 days** |
| Fix or mitigation plan | Depends on severity; critical issues are prioritized |

We coordinate disclosure with reporters and aim to publish a fix before public disclosure when possible.

**Maintainers:** Reza Ali ([@imRezaAlie](https://github.com/imRezaAlie)) — rezaalie70@gmail.com. Governance and succession are described in [GOVERNANCE.md](GOVERNANCE.md).

## Updateable reused components

Externally maintained components are identified in machine-readable manifests and updated through standard package managers (with a documented inventory for static theme assets).

### PHP dependencies (Composer)

| Identify | Update |
|----------|--------|
| [composer.json](composer.json) `require` / `require-dev` | `composer update <package>` or merge a [Dependabot](.github/dependabot.yml) PR |
| Locked versions in [composer.lock](composer.lock) | Regenerated on `composer update`; commit the lock file |

```bash
composer update              # update within version constraints
composer audit               # check advisories after updating
composer test                # verify before merge
```

### Frontend build dependencies (npm)

| Identify | Update |
|----------|--------|
| [package.json](package.json) `devDependencies` | `npm update` or merge a Dependabot PR |
| Locked versions in [package-lock.json](package-lock.json) | Regenerated on `npm install` / `npm update`; commit the lock file |

```bash
npm ci && npm audit          # install and check advisories
npm run build                # rebuild assets after updates
```

Libraries in the Vite pipeline (e.g. Bootstrap) are sourced from npm, not copied manually.

### Static admin theme assets (`public/vendor/`)

PHP and Node libraries are not committed (`vendor/` and `node_modules/` are gitignored). The admin UI also includes a small set of **committed** third-party scripts and styles:

| Path | Component | Typical update |
|------|-----------|----------------|
| `public/vendor/apexchart/` | ApexCharts | Replace from [official release](https://github.com/apexcharts/apexcharts.js) |
| `public/vendor/bootstrap-select/` | Bootstrap Select | Replace from [upstream dist](https://github.com/snapappointments/bootstrap-select) |
| `public/vendor/chart-js/` | Chart.js | Replace from [Chart.js releases](https://github.com/chartjs/Chart.js) |
| `public/vendor/datatables/` | DataTables + Buttons | Replace from [DataTables download](https://datatables.net/download) |
| `public/vendor/metismenu/` | MetisMenu | Replace from [MetisMenu releases](https://github.com/onokumus/metismenu) |
| `public/vendor/global/` | Theme bundle (`global.min.js`) | Replace when upgrading the admin theme |
| `public/landing/assets/js/vendors/bootstrap.min.js` | Bootstrap (landing) | Align with npm `bootstrap` or [getbootstrap.com](https://getbootstrap.com/) |

When a security advisory affects a static asset, replace files from upstream and note the change in the commit or release notes. Prefer npm + Vite for new frontend work.

## Dependency monitoring

OpenITS tracks vulnerabilities in third-party dependencies listed in [composer.json](composer.json), [composer.lock](composer.lock), [package.json](package.json), and [package-lock.json](package-lock.json), plus the static assets inventoried above.

### Automated monitoring

| Mechanism | What it checks | Frequency |
|-----------|----------------|-----------|
| [Dependabot](.github/dependabot.yml) | Composer and npm dependency updates and security advisories | Weekly |
| [Dependency audit workflow](.github/workflows/audit.yml) | `composer audit` and `npm audit --audit-level=critical` | Every push/PR to `main`, plus weekly schedule |
| GitHub dependency graph | Alerts on the repository (when enabled in repo settings) | Continuous |

### Maintainer response

When a known vulnerability is reported (by Dependabot, `composer audit`, `npm audit`, or GitHub alerts):

1. **Triage** — confirm whether OpenITS code paths use the affected package/API.
2. **Fix** — merge dependency updates (prefer Dependabot PRs) or patch releases; run `composer test` before merge.
3. **Verify unexploitable** — if no fix is available yet, document why the issue does not apply (e.g., feature unused, mitigated by configuration) in the Dependabot PR or a linked issue; re-check on the next audit run.
4. **Disclose** — critical fixes may warrant a [GitHub Security Advisory](https://github.com/imRezaAlie/openits/security/advisories) and release notes.

### Local checks

```bash
composer audit          # PHP dependency advisories
npm audit               # after npm ci (dev/build dependencies only)
```

### Known open advisories (tracked)

Maintainers review `composer audit` and Dependabot findings. As of the latest dependency update:

| Advisory | Package | Mitigation / plan |
|----------|---------|-------------------|
| [GHSA-5vg9-5847-vvmq](https://github.com/advisories/GHSA-5vg9-5847-vvmq) (CRLF in default email rule) | `laravel/framework` 11.x | Fix requires Laravel **12.60+**; tracked for a future major upgrade. OpenITS uses Form Request validation on user-facing inputs; no reliance on Laravel’s default `email` rule alone for security boundaries. |
| [GHSA-crmm-hgp2-wgrp](https://github.com/advisories/GHSA-crmm-hgp2-wgrp) (signed URL path confusion) | `laravel/framework` 11.x | Fix requires Laravel **12.61+**; limited use of temporary signed URLs in OpenITS. Re-evaluate on Laravel 12 upgrade. |
| Vite / esbuild (dev server) | npm devDependencies | **Dev-only** (`npm run dev`); not used in production deployments that serve pre-built assets. Moderate findings accepted until a non-breaking Vite major upgrade. |

Re-run `composer audit` after merging Dependabot PRs or before releases.

## Cryptography practices

OpenITS follows these cryptographic practices:

- **Password storage:** Laravel's `Hash` facade with **bcrypt** (configurable rounds via `BCRYPT_ROUNDS`, default 12). LDAP passwords are never stored locally.
- **Session encryption:** Optional via `SESSION_ENCRYPT=true` in production.
- **API tokens:** Laravel Sanctum with configurable expiration (`SANCTUM_EXPIRATION`).
- **Random values:** PHP's `random_bytes()` / Laravel's `Str::random()` for tokens and keys.
- **Transport:** TLS/HTTPS required in production; LDAPS or STARTTLS for directory authentication.
- **Algorithms:** We do not implement custom cryptography. Laravel and PHP standard library primitives are used. Avoid MD5, SHA-1, DES, and RC4 for security-sensitive operations.

## Security hardening recommendations

When deploying OpenITS in production:

- Set `APP_DEBUG=false`
- Use HTTPS with `SESSION_SECURE_COOKIE=true`
- Keep `REGISTRATION_ENABLED=false` unless self-service sign-up is required
- Use LDAPS or STARTTLS; keep `LDAP_ALLOW_INSECURE=false`
- Set `DEPLOYMENT_ENABLED=false` after initial setup
- Use Redis for `CACHE_STORE` in production (reliable rate limiting)
- Change the default seeded admin password immediately
- Restrict admin accounts (`is_admin`) to trusted personnel

See the [Security section in README.md](README.md#security) for full configuration guidance.

For the formal security assurance case (threat model, trust boundaries, and control arguments), see **[ASSURANCE_CASE.md](ASSURANCE_CASE.md)**.

## Credit for security reporters

When we resolve a reported vulnerability, we **credit the reporter(s)** unless they ask to remain anonymous:

| Where credit appears | What we include |
|----------------------|-----------------|
| [GitHub Security Advisories](https://github.com/imRezaAlie/openits/security/advisories) | Reporter name or handle in the advisory text (when published) |
| [GitHub Releases](https://github.com/imRezaAlie/openits/releases) | Thank-you line in release notes for the fix |
| This document | Row in the table below for the last 12 months |

Reporters may request anonymity at any time; in that case we omit their name or list them as **Anonymous reporter**.

### Resolved vulnerabilities (last 12 months)

*Rolling window: vulnerabilities **resolved** between **24 June 2025** and **24 June 2026**.*

| Date resolved | Summary | Reporter | Advisory / release |
|---------------|---------|----------|-------------------|
| — | *No vulnerabilities resolved in this period* | — | — |

This table is updated when a fix is shipped. Report issues via [Reporting a vulnerability](#reporting-a-vulnerability) above.

## Bug bounty

OpenITS does not currently operate a paid bug bounty program. We appreciate responsible disclosure; see [Credit for security reporters](#credit-for-security-reporters) for how reporters are acknowledged.
