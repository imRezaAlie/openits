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

## Response timeline

| Stage | Target |
|-------|--------|
| Initial acknowledgment | Within **48 hours** |
| Status update | Within **7 days** |
| Fix or mitigation plan | Depends on severity; critical issues are prioritized |

We will coordinate disclosure with you and aim to publish a fix before public disclosure when possible.

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

## Bug bounty

OpenITS does not currently operate a paid bug bounty program. We appreciate responsible disclosure and will acknowledge reporters in release notes when appropriate (with permission).
