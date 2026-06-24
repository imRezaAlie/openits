# Security Assurance Case

This document explains **why** OpenITS security requirements are met. It is the project’s formal assurance case for the [OpenSSF Best Practices Badge](https://www.bestpractices.dev/).

**Scope:** Self-hosted OpenITS web application (Laravel 11, PHP 8.2+), including web UI, REST authentication API (Sanctum), LDAP/Google login, and C4 diagram sharing.

**Related documents:** [SECURITY.md](SECURITY.md) · [README Security](README.md#security) · [CONTRIBUTING.md](CONTRIBUTING.md)

---

## 1. Security requirements

OpenITS is designed to protect enterprise architecture and integration documentation. Core security requirements:

| ID | Requirement |
|----|-------------|
| **SR-1** | Only authenticated users access private data; role-based access for administration. |
| **SR-2** | Credentials and secrets are not stored or transmitted in cleartext. |
| **SR-3** | Authentication endpoints resist brute-force and credential-stuffing attacks. |
| **SR-4** | User input is validated and output encoded/sanitized to prevent injection and XSS. |
| **SR-5** | Dependencies are monitored; vulnerabilities are triaged and remediated. |
| **SR-6** | Security issues can be reported privately and are handled under a documented process. |
| **SR-7** | Production deployments use secure defaults (no debug leakage, TLS, hardened sessions). |

---

## 2. Threat model

### 2.1 Assets

| Asset | Sensitivity |
|-------|-------------|
| User passwords (local accounts) | High |
| API tokens (Sanctum) | High |
| LDAP bind credentials (transient) | High |
| Architecture diagrams, APIs, ADRs, integration maps | Business confidential |
| Admin configuration (LDAP, OAuth, registration flags) | High |
| Application secrets (`.env`, `APP_KEY`) | Critical |

### 2.2 Adversaries

| Actor | Capability | Motivation |
|-------|------------|------------|
| **Anonymous Internet attacker** | Network access to public endpoints | Credential theft, defacement, data exfiltration |
| **Authenticated low-privilege user** | Valid session or API token | Horizontal privilege escalation, unauthorized edits |
| **Malicious insider / compromised admin** | Admin account | Full data access, misconfiguration |
| **Abuse of identity providers** | LDAP/Google account or misconfigured auto-provision | Unauthorized account creation |

### 2.3 Threats (STRIDE-oriented)

| Threat | Example | Primary controls |
|--------|---------|------------------|
| **Spoofing** | Stolen session cookie, forged API token | HTTPS, `SESSION_SECURE_COOKIE`, Sanctum expiration, session regeneration on SSO login |
| **Tampering** | Unauthorized C4 or API record changes | Authentication middleware, authorization checks, Eloquent ORM (parameterized queries) |
| **Repudiation** | Deny publishing a diagram change | Laravel Auditing (`owen-it/laravel-auditing`), LDAP audit logs |
| **Information disclosure** | `.env` leak, verbose errors, IDOR | `APP_DEBUG=false`, private vulnerability reporting, access control on admin routes |
| **Denial of service** | Login flooding | `LoginThrottleService`, IP-level limits, Redis-backed cache in production |
| **Elevation of privilege** | Non-admin reaches `/admin/settings` | `is_admin` checks, middleware, Form Request authorization |

### 2.4 Out of scope

- Physical security of the host running OpenITS
- Compromise of the operator’s MySQL/LDAP/Google infrastructure
- Supply-chain attacks on Packagist/npm (partially mitigated via [dependency monitoring](SECURITY.md#dependency-monitoring))

Assumptions: operators deploy behind TLS, protect `.env`, patch the OS, and restrict network access to admin interfaces.

---

## 3. Trust boundaries

```
                    ┌─────────────────────────────────────────┐
   Untrusted        │           TRUST BOUNDARY 1              │  Trusted
   Internet  ──────►│  Reverse proxy / TLS termination        │  Application
                    │  (operator responsibility)              │  (OpenITS)
                    └──────────────────┬──────────────────────┘
                                       │
                    ┌──────────────────▼──────────────────────┐
                    │  Laravel HTTP kernel                  │
                    │  · SecurityHeaders middleware         │
                    │  · Auth / Sanctum / session           │
                    │  · Rate limiting (login)              │
                    └──────────────────┬──────────────────────┘
                                       │
         ┌─────────────────────────────┼─────────────────────────────┐
         │                             │                             │
         ▼                             ▼                             ▼
  ┌──────────────┐            ┌──────────────┐            ┌──────────────┐
  │ TB2: Public  │            │ TB3: Auth    │            │ TB4: Admin   │
  │ routes       │            │ user session │            │ is_admin     │
  │ (login,      │            │ (C4, APIs,   │            │ (settings,   │
  │  landing,    │            │  domains)    │            │  users)      │
  │  C4 share*)  │            └──────┬───────┘            └──────┬───────┘
  └──────────────┘                   │                             │
         *optional password          │                             │
                                     ▼                             ▼
                    ┌─────────────────────────────────────────┐
                    │  TB5: Data plane                        │
                    │  MySQL/SQLite · Redis cache · LDAP/     │
                    │  Google (external IdP) · filesystem       │
                    └─────────────────────────────────────────┘
```

| Boundary | What crosses it | Trust decision |
|----------|-----------------|----------------|
| **TB1** Client ↔ OpenITS | HTTP(S) requests, cookies, API tokens | TLS required in production; cookies marked `Secure` when configured |
| **TB2** Anonymous ↔ app | Login forms, public landing, optional C4 share links | Rate limits; share links are read-only with optional password (`bcrypt`) |
| **TB3** Authenticated user ↔ app | CRUD on architecture data | Session or Sanctum token; per-resource authorization |
| **TB4** Admin ↔ app | Settings, user management, LDAP/Google config | `is_admin` gate on routes and controllers |
| **TB5** App ↔ external systems | SQL, LDAP, OAuth, mail | Parameterized queries; LDAP filter escaping; LDAP host validation (SSRF mitigation) |

---

## 4. Secure design principles applied

| Principle | How OpenITS applies it | Evidence |
|-----------|------------------------|----------|
| **Secure defaults** | Registration off by default; deployment route disabled after setup; insecure LDAP disallowed in production guidance | [README Security](README.md#security), `.env.example` |
| **Least privilege** | Admin-only settings and user management; collaboration permissions on C4 comments/approvals | [README Access control](README.md#access-control) |
| **Defense in depth** | Rate limiting + security headers + session hardening + input validation layers | `LoginThrottleService`, `SecurityHeaders` middleware, Form Requests |
| **Fail secure** | Failed logins lock out; disabled auth providers reject requests; auth required for private routes | `LoginController`, `GoogleAuthController`, `LdapAuthController`, route middleware |
| **Complete mediation** | Every protected route goes through Laravel auth/authorization middleware | `routes/web.php`, `routes/api.php` |
| **Economy of mechanism** | No custom crypto; Laravel `Hash`, Sanctum, Socialite, standard LDAP | [SECURITY.md cryptography](SECURITY.md#cryptography-practices) |
| **Separation of duties** | Business logic in `app/Services/`; validation in Form Requests; thin controllers | [CONTRIBUTING.md coding standards](CONTRIBUTING.md#coding-standards) |
| **Open design** | Security behavior documented publicly; vulnerabilities reported privately | [SECURITY.md](SECURITY.md) |

---

## 5. Common implementation weaknesses countered

Mapping to typical CWE/OWASP-style weaknesses and OpenITS countermeasures:

| Weakness | Countermeasure | Implementation / doc |
|----------|----------------|----------------------|
| **SQL injection** | Parameterized queries via Eloquent; no string-concatenated SQL in app code | Eloquent models; CONTRIBUTING: avoid raw SQL |
| **XSS** | Blade escaping; Markdown rendered with DOMPurify sanitization | README: Markdown XSS |
| **CSRF** | Laravel CSRF tokens on state-changing web forms | Laravel `VerifyCsrfToken` middleware |
| **Broken authentication** | bcrypt passwords; login rate limits; session regeneration on SSO | `LoginThrottleService`, `config/login.php`, tests in `LoginThrottleTest` |
| **Sensitive data exposure** | TLS, optional session encryption, no LDAP password storage | `SESSION_ENCRYPT`, SECURITY.md |
| **Security misconfiguration** | Production checklist (`APP_DEBUG`, `DEPLOYMENT_ENABLED`, registration flags) | README Security, SECURITY hardening |
| **IDOR / missing function access control** | `is_admin` for admin areas; collaboration rules on C4 | README Access control |
| **LDAP injection** | Filter escaping on LDAP search/bind identifiers | README: LDAP filter escaping |
| **SSRF (LDAP host)** | Host validation blocks private/reserved addresses on admin LDAP tests | README Transport & session hardening |
| **Weak cryptography** | bcrypt / `random_bytes()`; ban MD5/SHA-1/DES/RC4 for security use | SECURITY.md, README |
| **Vulnerable dependencies** | Dependabot, `composer audit`, CI audit workflow | [SECURITY.md dependency monitoring](SECURITY.md#dependency-monitoring) |
| **Missing security logging** | LDAP audit logging; model auditing package | `LdapLog`, `owen-it/laravel-auditing` |
| **Session fixation** | Session regeneration on LDAP/Google login | README |
| **Brute force** | Per-credential and per-IP throttling | `LoginThrottleService`, feature tests |

Automated tests cover authentication, LDAP/Google flows, and login throttling (`tests/Feature/`). CI runs on every push/PR to `main` ([`.github/workflows/tests.yml`](.github/workflows/tests.yml)).

---

## 6. Assurance argument summary

| Security requirement | Argument that it is met |
|---------------------|-------------------------|
| **SR-1** Access control | Auth middleware on private routes; `is_admin` for administration; documented trust boundaries (§3). |
| **SR-2** Secret handling | bcrypt for local passwords; TLS/LDAPS mandated for production; secrets in `.env` not committed. |
| **SR-3** Auth abuse resistance | `LoginThrottleService` with configurable limits; covered by automated tests. |
| **SR-4** Injection/XSS | Eloquent ORM, Form Requests, CSRF, DOMPurify for Markdown. |
| **SR-5** Dependency safety | Machine-readable manifests, Dependabot, audit CI, documented response process. |
| **SR-6** Vulnerability handling | [SECURITY.md vulnerability response process](SECURITY.md#vulnerability-response-process). |
| **SR-7** Secure deployment | Documented production env checklist; secure defaults in `.env.example`. |

**Residual risks:** Laravel framework advisories without 11.x patches (tracked in SECURITY.md); static `public/vendor/` assets require manual updates; operators must correctly configure TLS and secrets.

**Review:** Maintainers update this assurance case when threat model or major security controls change. Last reviewed: **June 2026**.

---

## 7. References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP ASVS](https://owasp.org/www-project-application-security-verification-standard/) (informal alignment, not formally certified)
- [OpenSSF Best Practices — secure design](https://www.bestpractices.dev/en/criteria)
